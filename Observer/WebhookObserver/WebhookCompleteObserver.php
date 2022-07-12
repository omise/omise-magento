<?php

namespace Omise\Payment\Observer\WebhookObserver;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omise\Payment\Model\Api\Event as ApiEvent;
use Omise\Payment\Model\Order;
use Omise\Payment\Model\Event\Charge\Complete as EventChargeComplete;
use Omise\Payment\Helper\OmiseEmailHelper as EmailHelper;
use Omise\Payment\Helper\OmiseHelper as Helper;
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Model\Api\Charge as ApiCharge;
use Magento\Sales\Model\Order as MagentoOrder;
use Omise\Payment\Observer\WebhookObserver\WebhookObserver;
use Magento\Sales\Model\Order\Payment\Transaction;

class WebhookCompleteObserver extends WebhookObserver
{
    /**
     * @param \Omise\Payment\Model\Api\Event $apiEvent;
     * @param \Omise\Payment\Model\Order $order
     * @param \Omise\Payment\Helper\OmiseEmailHelper $emailHelper
     * @param \Omise\Payment\Helper\OmiseHelper $helper
     * @param \Omise\Payment\Model\Config\Config $config
     */
    public function __construct(
        ApiEvent $apiEvent,
        Order $order,
        EmailHelper $emailHelper,
        Helper $helper,
        Config $config
    ) {
        parent::__construct($apiEvent, $order, $config);
        $this->emailHelper = $emailHelper;
        $this->helper = $helper;
    }

    /**
     * @param Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->setUpExecute($observer)) {
            return;
        }

        $isPaymentPending = $this->orderData->getState() === MagentoOrder::STATE_PENDING_PAYMENT;

        if ($this->orderData->isPaymentReview() || $isPaymentPending) {
            if ($this->charge->isFailed()) {
                $this->cancelOrder();
                return;
            }

            $isAwaitingCapture = $this->charge->isAwaitCapture();

            // Successful payment
            if ($this->charge->isSuccessful() || $isAwaitingCapture) {
                $this->processOrder(!$isAwaitingCapture);
            }

            return;
        }

        // To handle the situation where charge is manually updated as successful
        // from Omise dashboard after the order is canceled.
        if ($this->orderData->getState() === MagentoOrder::STATE_CANCELED && $this->charge->isSuccessful()) {
            $this->processCancelledOrder();
        }
    }

    /**
     * Add transaction comments to the order with message for authorise or capture
     *
     * @param boolean $isCaptured
     * @param string $amount
     */
    private function transactionCommentToOrder(bool $isCaptured, string $amount)
    {
        if ($isCaptured) {
            $transaction = $this->payment->addTransaction(Transaction::TYPE_PAYMENT, $this->invoice);
            $comment = __('Amount of %1 has been paid via Omise Payment Gateway.', $amount);
        } else {
            $transaction = $this->payment->addTransaction(Transaction::TYPE_AUTH, $this->invoice);
            $comment = __('Authorized amount of %1 via Omise Payment Gateway (3-D Secure payment).', $amount);
        }

        $this->payment->addTransactionCommentsToOrder($transaction, $comment);
    }

    /**
     * Complete the transaction and set order status as processing
     *
     * @param bool $isCaptured Is capture pending
     */
    private function processOrder($isCaptured = true)
    {
        // Update order state and status.
        $this->orderData->setState(MagentoOrder::STATE_PROCESSING);
        $defaultStatus = $this->orderData->getConfig()->getStateDefaultStatus(MagentoOrder::STATE_PROCESSING);
        $this->orderData->setStatus($defaultStatus);

        $this->invoice = $this->helper->createInvoiceAndMarkAsPaid(
            $this->orderData,
            $this->charge->id,
            $isCaptured
        );

        $this->emailHelper->sendInvoiceAndConfirmationEmails($this->orderData);

        // addTransactionCommentsToOrder with message for authorise or capture
        $amount = $isCaptured ? $this->invoice->getBaseGrandTotal() : $this->orderData->getTotalDue();
        $this->transactionCommentToOrder($isCaptured, $this->orderData->getBaseCurrency()->formatTxt($amount));

        $this->orderData->save();
    }

    /**
     * Reverse the item status to ordered and process the order. Not reversing the item status
     * changes the order status to closed/complete even if we set it to be processing
     *
     * @param OmiseHelper $helper
     * @param OmiseEmailHelper $emailHelper
     */
    private function processCancelledOrder()
    {
        $this->reverseCancelledItems();
        $this->processOrder();
    }

    /**
     * Setting the item status from cancelled to ordered to properly set the order status
     *
     * @return void
     */
    private function reverseCancelledItems()
    {
        $items = $this->orderData->getAllItems();

        foreach ($items as $item) {
            $item->setQtyCanceled(0);
            $item->save();
        }
    }

    /**
     * Cancel the order by registering payment failure message
     */
    private function cancelOrder()
    {
        if ($this->orderData->hasInvoices()) {
            $this->invoice = $this->orderData->getInvoiceCollection()->getLastItem();
            $this->invoice->cancel();
            $this->orderData->addRelatedObject($this->invoice);
        }

        $orderMessage = __(
            'Payment failed. %1, please contact our support if you have any questions.',
            ucfirst($this->charge->failure_message)
        );
        $this->orderData->registerCancellation($orderMessage)->save();
    }
}
