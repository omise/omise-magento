<?php
namespace Omise\Payment\Model;

use Magento\Framework\Exception\LocalizedException;
use Omise\Payment\Helper\OmiseHelper as Helper;
use Omise\Payment\Helper\OmiseEmailHelper as EmailHelper;
use Magento\Sales\Model\Order;
use Omise\Payment\Model\Config\Cc as Config;

class SyncStatus
{
    const STATUS_SUCCESSFUL = 'successful';
    const STATUS_FAILED     = 'failed';
    const STATUS_PENDING    = 'pending';
    const STATUS_EXPIRED    = 'expired';
    const STATUS_REVERSED   = 'reversed';
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var EmailHelper
     */
    protected $emailHelper;

    /**
     * @param Helper $helper
     * @param EmailHelper $emailHelper
     */
    public function __construct(
        Helper $helper,
        EmailHelper $emailHelper,
        Config $config
    ) {
        $this->helper = $helper;
        $this->emailHelper = $emailHelper;
        $this->config = $config;
    }

    /**
     * @param Order $order
     * @return void
     */
    public function cancelOrderInvoice($order)
    {
        if ($order->hasInvoices()) {
            $invoice = $order->getInvoiceCollection()->getLastItem();
            $invoice->cancel();
            $order->addRelatedObject($invoice);
        }
    }
    
    /**
     * @param Order $order
     * @return void
     * @throws LocalizedException
     */
    public function sync($order)
    {
        $chargeId = $this->helper->getOrderChargeId($order);

        if (!$chargeId) {
            throw new LocalizedException(__('Unable to find Omise charge ID'));
        }

        // set the store to fetch configuration values from store specific to the order
        $this->config->setStoreId($order->getStore()->getId());
        $charge = \OmiseCharge::retrieve($chargeId, $this->config->getPublicKey(), $this->config->getSecretKey());

        switch ($charge['status']) {
            case self::STATUS_SUCCESSFUL:
                $this->markPaymentSuccessful($order, $charge);
                break;
            case self::STATUS_FAILED:
                $this->markPaymentFailed($order, $charge);
                break;
            case self::STATUS_PENDING:
                $this->markOrderPending($order);
                break;
            case self::STATUS_EXPIRED:
                $this->markPaymentExpired($order);
                break;
            case self::STATUS_REVERSED:
                $this->markPaymentReversed($order);
                break;
            default:
                throw new LocalizedException(
                    __('Cannot read the payment status. Please try sync again or contact Omise support team
                        at support@omise.co if you have any questions.')
                );
        }
    }

    /**
     * @param Order $order
     * @param array $charge
     * @return void
     */
    private function markPaymentSuccessful($order, $charge)
    {
        $refundKeyExist = array_key_exists('refunds', $charge);
        $orderStateNotClosed = $order->getState() != Order::STATE_CLOSED;

        if ($refundKeyExist && $orderStateNotClosed) {
            $dataKeyExist = array_key_exists('data', $charge['refunds']);

            if($dataKeyExist && $charge['refunds']['data']) {
                return $this->refund($order, $charge);
            }
        }

        // Payment will be already processed for the following states
        $orderStates = [Order::STATE_COMPLETE, Order::STATE_CLOSED, Order::STATE_PROCESSING];

        if (!in_array($order->getState(), $orderStates)) {
            $order->setState(Order::STATE_PROCESSING);
            $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));

            $invoice = $this->helper->createInvoiceAndMarkAsPaid($order, $charge['id']);
            $this->emailHelper->sendInvoiceAndConfirmationEmails($order);

            $order->addStatusHistoryComment(
                __(
                    'Omise: Payment successful.<br/>An amount %1 %2 has been paid (manual sync).',
                    number_format($order->getGrandTotal(), 2, '.', ''),
                    $order->getOrderCurrencyCode()
                )
            );

            $order->save();
        }
    }

    /**
     * @param Order $order
     * @param array $charge
     * @return void
     */
    private function refund($order, $charge)
    {
        $refundedAmount = isset($charge['refunded_amount'])
            ? $charge['refunded_amount']
            : $charge['refunded'];

        if ($charge['funding_amount'] == $refundedAmount) {
            $order->setState(Order::STATE_CLOSED);
            $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CLOSED));
        }

        $order->addStatusHistoryComment(
            __(
                'Omise: Payment refunded.<br/>An amount of %1 %2 has been refunded (manual sync).',
                number_format($charge['refunded_amount']/100, 2, '.', ''),
                $order->getOrderCurrencyCode()
            )
        );

        $order->save();
    }

    /**
     * @param Order $order
     * @param array $charge
     */
    private function markPaymentFailed($order, $charge)
    {
        $this->cancelOrderInvoice($order);
        $order->registerCancellation(
            __(
                'Omise: Payment failed.<br/>%1 (code: %2) (manual sync).',
                $charge['failure_message'],
                $charge['failure_code']
            )
        )->save();
        $order->save();
    }

    /**
     * @param Order $order
     */
    private function markOrderPending($order)
    {
        $order->addStatusHistoryComment(
            __('Omise: Payment is still in progress.<br/>
                You might wait for a moment before click sync the status again or contact Omise support
                team at support@omise.co if you have any questions (manual sync).')
        );
        if ($order->getState() != Order::STATE_PENDING_PAYMENT) {
            $order->setState(Order::STATE_PENDING_PAYMENT)->setStatus(Order::STATE_PENDING_PAYMENT);
        }
        $order->save();
    }

    /**
     * @param Order $order
     */
    private function markPaymentExpired($order)
    {
        $this->cancelOrderInvoice($order);
        $order->registerCancellation(__('Omise: Payment expired. (manual sync).'))
            ->save();
    }

    /**
     * @param Order $order
     */
    private function markPaymentReversed($order)
    {
        $order->addStatusHistoryComment(__('Omise: Payment reversed. (manual sync).'));

        if ($order->getState() != Order::STATE_CANCELED) {
            $order->setState(Order::STATE_CANCELED)->setStatus(Order::STATE_CANCELED);
        }

        $order->save();
    }
}
