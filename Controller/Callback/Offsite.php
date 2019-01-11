<?php
namespace Omise\Payment\Controller\Callback;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction;
use Omise\Payment\Model\Omise;
use Omise\Payment\Model\Api\Charge;
use Omise\Payment\Model\Config\Internetbanking;
use Omise\Payment\Model\Config\Alipay;
use Omise\Payment\Model\Config\Installment;

class Offsite extends Action
{
    /**
     * @var string
     */
    const PATH_CART    = 'checkout/cart';
    const PATH_SUCCESS = 'checkout/onepage/success';

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * @var \Omise\Payment\Model\Omise
     */
    protected $omise;

    /**
     * @var \Omise\Payment\Model\Api\Charge
     */
    protected $charge;

    public function __construct(
        Context $context,
        Session $session,
        Omise   $omise,
        Charge  $charge
    ) {
        parent::__construct($context);

        $this->session = $session;
        $this->omise   = $omise;
        $this->charge  = $charge;

        $this->omise->defineUserAgent();
        $this->omise->defineApiVersion();
        $this->omise->defineApiKeys();
    }

    /**
     * @return void
     */
    public function execute()
    {
        $order = $this->session->getLastRealOrder();

        if (! $order->getId()) {
            $this->messageManager->addErrorMessage(__('The order session no longer exists, please make an order again or contact our support if you have any questions.'));

            return $this->redirect(self::PATH_CART);
        }

        if ($order->getState() !== Order::STATE_PENDING_PAYMENT) {
            $this->invalid($order, __('Invalid order status, cannot validate the payment. Please contact our support if you have any questions.'));

            return $this->redirect(self::PATH_CART);
        }

        if (! $payment = $order->getPayment()) {
            $this->invalid($order, __('Cannot retrieve a payment detail from the request. Please contact our support if you have any questions.'));

            return $this->redirect(self::PATH_CART);
        }
        
        $paymentMethod = $payment->getMethod();
        if (! in_array($paymentMethod, [Alipay::CODE, Internetbanking::CODE, Installment::CODE])) {
            $this->invalid($order, __('Invalid payment method. Please contact our support if you have any questions.'));

            return $this->redirect(self::PATH_CART);
        }

        if (! $charge_id = $payment->getAdditionalInformation('charge_id')) {
            $this->cancel($order, __('Cannot retrieve a charge reference id. Please contact our support to confirm your payment.'));
            $this->session->restoreQuote();

            return $this->redirect(self::PATH_CART);
        }

        if (! $order->hasInvoices()) {
            $this->cancel($order, __('Cannot create an invoice. Please contact our support to confirm your payment.'));
            $this->session->restoreQuote();

            return $this->redirect(self::PATH_CART);
        }

        try {
            $charge = $this->charge->find($charge_id);

            if (! $charge instanceof \Omise\Payment\Model\Api\BaseObject) {
                throw new Exception('Couldn\'t retrieve charge transaction. Please contact administrator.');
            }

            if ($charge instanceof \Omise\Payment\Model\Api\Error) {
                throw new Exception($charge->getMessage());
            }

            if ($charge->isFailed()) {
                throw new Exception('Payment failed. ' . ucfirst($charge->failure_message) . ', please contact our support if you have any questions.');
            }

            $payment->setTransactionId($charge->id);
            $payment->setLastTransId($charge->id);

            if ($charge->isSuccessful()) {
                // Update order state and status.
                $order->setState(Order::STATE_PROCESSING);
                $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));

                $invoice = $this->invoice($order);
                $invoice->setTransactionId($charge->id)->pay()->save();
                
                switch ($paymentMethod) {
                    case Internetbanking::CODE:
                        $dispPaymentMethod = "Internet Banking";
                        break;
                    case Installment::CODE:
                        $dispPaymentMethod = "Installment";
                        break;
                    case Alipay::CODE:
                        $dispPaymentMethod = "Alipay";
                        break;
                }
                
                // Add transaction.
                $payment->addTransactionCommentsToOrder(
                    $payment->addTransaction(Transaction::TYPE_PAYMENT, $invoice),
                    __(
                        "Amount of %1 has been paid via Omise $dispPaymentMethod payment",
                        $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal())
                    )
                );

                $order->save();
                return $this->redirect(self::PATH_SUCCESS);
            }

            // Update order state and status.
            $order->setState(Order::STATE_PAYMENT_REVIEW);
            $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PAYMENT_REVIEW));

            // Add transaction.
            $transaction = $payment->addTransaction(Transaction::TYPE_PAYMENT);
            $transaction->setIsClosed(false);
            $payment->addTransactionCommentsToOrder(
                $transaction,
                __('The payment has been processing.<br/>Due to the Bank process, this might takes a few seconds or up-to an hour. Please click "Accept" or "Deny" the payment manually once the result has been updated (you can check at Omise Dashboard).')
            );

            $order->save();

            // TODO: Should redirect users to a page that tell users that
            //       their payment is in review instead of success page.
            return $this->redirect(self::PATH_SUCCESS);
        } catch (Exception $e) {
            $this->cancel($order, $e->getMessage());

            return $this->redirect(self::PATH_CART);
        }
    }

    /**
     * @param  \Magento\Sales\Model\Order $order
     *
     * @return \Magento\Sales\Api\Data\InvoiceInterface
     */
    protected function invoice(Order $order)
    {
        return $order->getInvoiceCollection()->getLastItem();
    }

    /**
     * @param  string $path
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    protected function redirect($path)
    {
        return $this->_redirect($path, ['_secure' => true]);
    }

    /**
     * @param \Magento\Sales\Model\Order       $order
     * @param \Magento\Framework\Phrase|string $message
     */
    protected function invalid(Order $order, $message)
    {
        $order->addStatusHistoryComment($message);
        $order->save();

        $this->messageManager->addErrorMessage($message);
    }

    /**
     * @param \Magento\Sales\Model\Order       $order
     * @param \Magento\Framework\Phrase|string $message
     */
    protected function cancel(Order $order, $message)
    {
        $invoice = $this->invoice($order);
        $invoice->cancel();
        $order->addRelatedObject($invoice);

        $order->registerCancellation($message)->save();
        $this->messageManager->addErrorMessage($message);
    }
}
