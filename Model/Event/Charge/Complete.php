<?php

namespace Omise\Payment\Model\Event\Charge;

use Magento\Sales\Model\Order as MagentoOrder;
use Magento\Sales\Model\Order\Payment\Transaction;
use Omise\Payment\Model\Order;
use Omise\Payment\Model\Api\Event as ApiEvent;
use Omise\Payment\Model\Api\Charge as ApiCharge;
use Omise\Payment\Helper\OmiseEmailHelper;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Model\Config\Cc as Config;

class Complete
{
    /**
     * @var string  of an event name.
     */
    const CODE = 'charge.complete';

    /**
     * @var Magento\Sales\Model\Order\Payment\Interceptor
     */
    private $payment;

    /**
     * @var Magento\Sales\Model\Order\Invoice\Interceptor
     */
    private $invoice;

    /**
     * @var Magento\Sales\Model\Order\Interceptor
     */
    private $order;

    /**
     * @var Omise\Payment\Model\Api\Charge
     */
    private $charge;

    /**
     * There are several cases with the following payment methods
     * that would trigger the 'charge.complete' event.
     *
     * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
     * Alipay
     * charge data in payload:
     *     [status: 'successful'], [authorized: 'true'], [paid: 'true']
     *     [status: 'failed'], [authorized: 'false'], [paid: 'false']
     *
     * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
     * Internet Banking
     * charge data in payload:
     *     [status: 'successful'], [authorized: 'true'], [paid: 'true']
     *     [status: 'failed'], [authorized: 'false'], [paid: 'false']
     *
     * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
     * Credit Card (3-D Secure)
     * CAPTURE = FALSE
     * charge data in payload could be one of these sets:
     *     [status: 'pending'], [authorized: 'true'], [paid: 'false']
     *     [status: 'failed'], [authorized: 'false'], [paid: 'false']
     *
     * CAPTURE = TRUE
     * charge data in payload could be one of these sets:
     *     [status: 'successful'], [authorized: 'true'], [paid: 'true']
     *     [status: 'failed'], [authorized: 'false'], [paid: 'false']
     *
     * @param  Omise\Payment\Model\Api\Event $event
     * @param  Omise\Payment\Model\Order     $order
     * @param  Omise\Payment\Helper\OmiseEmailHelper     $emailHelper
     * @param  Omise\Payment\Helper\OmiseHelper     $helper
     *
     * @return void
     */
    public function handle(
        ApiEvent $event,
        Order $order,
        OmiseEmailHelper
        $emailHelper,
        OmiseHelper $helper,
        Config $config
    ) {
        $this->charge = $event->data;

        if (! $this->charge instanceof ApiCharge || $this->charge->getMetadata('order_id') == null) {
            // TODO: Handle in case of improper response structure.
            return;
        }

        $this->order = $order->loadByIncrementId($this->charge->getMetadata('order_id'));

        if (! $this->order->getId()) {
            // TODO: Handle in case of improper response structure.
            return;
        }

        if (! $this->payment = $this->order->getPayment()) {
            // TODO: Handle in case of improper response structure.
            return;
        }

        if ($this->order->isPaymentReview() || $this->order->getState() === MagentoOrder::STATE_PENDING_PAYMENT) {
            if ($this->charge->isFailed()) {
                $this->cancelOrder();
                return;
            }

            $isAwaitingCapture = $this->charge->isAwaitCapture();

            // Successful payment
            if ($this->charge->isSuccessful() || $isAwaitingCapture) {
                $this->processOrder($helper, $emailHelper, !$isAwaitingCapture);
            }

            return;
        }

        // To handle the situation where charge is manually updated as successful
        // from Omise dashboard after the order is canceled.
        if ($this->order->getState() === MagentoOrder::STATE_CANCELED && $this->charge->isSuccessful()) {
            $this->processCancelledOrder($helper, $emailHelper);
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
     * @param OmiseHelper $helper
     * @param OmiseEmailHelper $emailHelper
     * @param bool $isCaptured Is capture pending
     */
    private function processOrder($helper, $emailHelper, $isCaptured = true)
    {
        // Update order state and status.
        $this->order->setState(MagentoOrder::STATE_PROCESSING);
        $this->order->setStatus($this->order->getConfig()->getStateDefaultStatus(MagentoOrder::STATE_PROCESSING));

        $this->invoice = $helper->createInvoiceAndMarkAsPaid($this->order, $this->charge->id, $isCaptured);
        $emailHelper->sendInvoiceAndConfirmationEmails($this->order);

        // addTransactionCommentsToOrder with message for authorise or capture
        $amount = $isCaptured ? $this->invoice->getBaseGrandTotal() : $this->order->getTotalDue();
        $this->transactionCommentToOrder($isCaptured, $this->order->getBaseCurrency()->formatTxt($amount));

        $this->order->save();
    }

    /**
     * Reverse the item status to ordered and process the order. Not reversing the item status
     * changes the order status to closed/complete even if we set it to be processing
     *
     * @param OmiseHelper $helper
     * @param OmiseEmailHelper $emailHelper
     */
    private function processCancelledOrder($helper, $emailHelper)
    {
        $this->reverseCancelledItems();
        $this->processOrder($helper, $emailHelper);
    }

    /**
     * Setting the item status from cancelled to ordered to properly set the order status
     *
     * @return void
     */
    private function reverseCancelledItems()
    {
        $items = $this->order->getAllItems();

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
        if ($this->order->hasInvoices()) {
            $this->invoice = $this->order->getInvoiceCollection()->getLastItem();
            $this->invoice->cancel();
            $this->order->addRelatedObject($this->invoice);
        }

        $orderMessage = __(
            'Payment failed. %1, please contact our support if you have any questions.',
            ucfirst($this->charge->failure_message)
        );
        $this->order->registerCancellation($orderMessage)->save();
    }
}
