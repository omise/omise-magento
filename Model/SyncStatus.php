<?php
namespace Omise\Payment\Model;

use Magento\Framework\Exception\LocalizedException;
use Omise\Payment\Helper\OmiseHelper as Helper;
use Omise\Payment\Helper\OmiseEmailHelper as EmailHelper;
use Magento\Sales\Model\Order;
use Omise\Payment\Model\Config\Cc as Config;
use Omise\Payment\Model\RefundSyncStatus;

#[\AllowDynamicProperties]
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
        Config $config,
        RefundSyncStatus $refundSyncStatus
    ) {
        $this->helper = $helper;
        $this->emailHelper = $emailHelper;
        $this->config = $config;
        $this->refundSyncStatus = $refundSyncStatus;
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
            throw new LocalizedException(__('Unable to find Opn Payments charge ID'));
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
                    __('Cannot read the payment status. Please try sync again or contact Opn Payments support team
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
        $orderStateNotClosed = $order->getState() != Order::STATE_CLOSED;

        if ($this->refundSyncStatus->shouldRefund($charge) && $orderStateNotClosed) {
            return $this->refundSyncStatus->refund($order, $charge);
        }

        // Payment will be already processed for the following states
        $orderStates = [Order::STATE_COMPLETE, Order::STATE_CLOSED, Order::STATE_PROCESSING];

        if (!in_array($order->getState(), $orderStates)) {
            if ($order->getState() === Order::STATE_CANCELED) {
                $this->reverseCancelledItems($order);
            }

            $order->setState(Order::STATE_PROCESSING);
            $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));

            $this->helper->createInvoiceAndMarkAsPaid($order, $charge['id']);
            $this->emailHelper->sendInvoiceAndConfirmationEmails($order);

            $order->addStatusHistoryComment(
                __(
                    'Opn Payments: Payment successful.<br/>An amount %1 %2 has been paid (manual sync).',
                    number_format($order->getGrandTotal(), 2, '.', ''),
                    $order->getOrderCurrencyCode()
                )
            );

            $order->save();
        }
    }

    /**
     * Setting the item status from cancelled to ordered to properly set the order status
     *
     * @return void
     */
    private function reverseCancelledItems($order)
    {
        $items = $order->getAllItems();

        foreach ($items as $item) {
            $item->setQtyCanceled(0);
            $item->save();
        }
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
                'Opn Payments: Payment failed.<br/>%1 (code: %2) (manual sync).',
                $charge['failure_message'],
                $charge['failure_code']
            )
        )->save();
    }

    /**
     * @param Order $order
     */
    private function markOrderPending($order)
    {
        $order->addStatusHistoryComment(
            __('Opn Payments: Payment is still in progress.<br/>
                You might wait for a moment before click sync the status again or contact Opn Payments support
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
        $order->registerCancellation(__('Opn Payments: Payment expired. (manual sync).'))
            ->save();
    }

    /**
     * @param Order $order
     */
    private function markPaymentReversed($order)
    {
        $this->cancelOrderInvoice($order);
        $order->registerCancellation(__('Opn Payments: Payment reversed. (manual sync).'))
            ->save();
    }
}
