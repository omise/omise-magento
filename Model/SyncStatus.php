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
        if ($chargeId) {
            $charge = \OmiseCharge::retrieve($chargeId, $this->config->getPublicKey(), $this->config->getSecretKey());
            switch ($charge['status']) {
                case self::STATUS_SUCCESSFUL:
                    $refunded_amount = isset($charge['refunded_amount'])
                        ? $charge['refunded_amount']
                        : $charge['refunded'];
                    if ($charge['funding_amount'] == $refunded_amount) {
                        $order->addStatusHistoryComment(
                            __(
                                'Omise: Payment refunded.<br/>An amount %1 %2 has been refunded (manual sync).',
                                number_format($order->getGrandTotal(), 2, '.', ''),
                                $order->getOrderCurrencyCode()
                            )
                        );
                    } else {
                        if ($order->getState() != Order::STATE_COMPLETE
                            && $order->getState() != Order::STATE_PROCESSING) {
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
                        }
                    }
                    $order->save();
                    break;
                case self::STATUS_FAILED:
                    $this->cancelOrderInvoice($order);
                    $order->registerCancellation(
                        __(
                            'Omise: Payment failed.<br/>%1 (code: %2) (manual sync).',
                            $charge['failure_message'],
                            $charge['failure_code']
                        )
                    )->save();
                    $order->save();
                    break;
                case self::STATUS_PENDING:
                    $order->addStatusHistoryComment(
                        __('Omise: Payment is still in progress.<br/>
                            You might wait for a moment before click sync the status again or contact Omise support
                            team at support@omise.co if you have any questions (manual sync).')
                    );
                    if ($order->getState() != Order::STATE_PENDING_PAYMENT) {
                        $order->setState(Order::STATE_PENDING_PAYMENT)->setStatus(Order::STATE_PENDING_PAYMENT);
                    }
                    $order->save();
                    break;
                case self::STATUS_EXPIRED:
                    $this->cancelOrderInvoice($order);
                    $order->registerCancellation(__('Omise: Payment expired. (manual sync).'))
                      ->save();
                    break;
                case self::STATUS_REVERSED:
                    $order->addStatusHistoryComment(__('Omise: Payment reversed. (manual sync).'));
                    if ($order->getState() != Order::STATE_CANCELED) {
                        $order->setState(Order::STATE_CANCELED)->setStatus(Order::STATE_CANCELED);
                    }
                    $order->save();
                    break;
                default:
                    throw new LocalizedException(
                        __('Cannot read the payment status. Please try sync again or contact Omise support team
                            at support@omise.co if you have any questions.')
                    );
            }
        } else {
            throw new LocalizedException(__('Unable to find Omise charge ID'));
        }
    }
}
