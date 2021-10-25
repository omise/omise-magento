<?php

namespace Omise\Payment\Model\Event\Charge;

use Magento\Sales\Model\Order as MagentoOrder;
use Magento\Sales\Model\Order\Payment\Transaction;
use Omise\Payment\Model\Order;
use Omise\Payment\Model\Api\Event as ApiEvent;
use Omise\Payment\Model\Api\Charge as ApiCharge;
use Omise\Payment\Helper\OmiseEmailHelper as EmailHelper;

class Complete
{
    /**
     * @var string  of an event name.
     */
    const CODE = 'charge.complete';

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
     *
     * @return void
     */
    public function handle(ApiEvent $event, Order $order, EmailHelper $emailHelper)
    {
        $charge = $event->data;

        if (! $charge instanceof ApiCharge || $charge->getMetadata('order_id') == null) {
            // TODO: Handle in case of improper response structure.
            return;
        }

        $order = $order->loadByIncrementId($charge->getMetadata('order_id'));
        if (! $order->getId()) {
            // TODO: Handle in case of improper response structure.
            return;
        }

        if (! $payment = $order->getPayment()) {
            // TODO: Handle in case of improper response structure.
            return;
        }

        if ($order->isPaymentReview() || $order->getState() === MagentoOrder::STATE_PENDING_PAYMENT) {
            if ($charge->isFailed()) {

                $order->registerCancellation(
                    __('Payment failed. ' . ucfirst($charge->failure_message) . ',
                        please contact our support if you have any questions.')
                )->save();
            }

            // Successful payment
            if ($charge->isSuccessful() || $charge->isAwaitCapture()) {
                // Update order state and status.
                $order->setState(MagentoOrder::STATE_PROCESSING);
                $order->setStatus($order->getConfig()->getStateDefaultStatus(MagentoOrder::STATE_PROCESSING));

                $invoice = $order->prepareInvoice();
                $invoice->register();
                $order->addRelatedObject($invoice);
                $invoice->setTransactionId($charge->id)->pay()->save();

                $emailHelper->sendInvoiceAndConfirmationEmails($order);

                // addTransactionCommentsToOrder with message for authorise or capture
                if ($charge->isAwaitCapture()) {
                    $payment->addTransactionCommentsToOrder(
                        $payment->addTransaction(Transaction::TYPE_AUTH, $invoice),
                        __(
                            'Authorized amount of %1 via Omise Payment Gateway (3-D Secure payment).',
                            $order->getBaseCurrency()->formatTxt($order->getTotalDue())
                        )
                    );
                } else {
                    $payment->addTransactionCommentsToOrder(
                        $payment->addTransaction(Transaction::TYPE_PAYMENT, $invoice),
                        __(
                            'Amount of %1 has been paid via Omise Payment Gateway',
                            $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal())
                        )
                    );
                }

                $order->save();
            }
        }
    }
}
