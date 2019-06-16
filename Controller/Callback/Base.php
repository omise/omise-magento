<?php
namespace Omise\Payment\Controller\Callback;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction;
use Omise\Payment\Model\Api\Charge;

abstract class Base extends Magento\Framework\App\Action\Action
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
        Magento\Framework\App\Action\Context $context,
        Magento\Checkout\Model\Session       $session,
        Omise\Payment\Model\Omise            $omise,
        Omise\Payment\Model\Api\Charge       $apiCharge
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
     * @return string
     */
    abstract protected function getPaymentMethodCode();

    /**
     * @return string
     */
    abstract protected function getPaymentMethodTitle();

    /**
     * /**
     * @param \Magento\Sales\Model\Order         $order
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param \Omise\Payment\Model\Api\Charge    $charge
     *
     * @return bool
     */
    abstract public function validate(Order $order, Invoice $invoice, Payment $payment, Charge $charge);

    /**
     * @return void
     */
    public function execute()
    {
        try {
            $order    = $this->tryLoadOrder();
            $invoice  = $this->tryLoadInvoice();
            $payment  = $this->tryLoadPayment($order);
            $chargeId = $this->tryLoadChargeId($payment);
            $charge   = $this->tryLoadCharge($chargeId);

            $payment->setTransactionId($charge->id);
            $payment->setLastTransId($charge->id);

            $this->validate($order, $invoice, $payment, $charge);

            if ($charge->isFailed()) {
                $note = 'Payment failed. ' . ucfirst($charge->failure_message) . ', please contact our support if you have any questions.';
                $this->paymentFailed($order, $invoice, $note);
                return $this->redirectToCart();
            }

            if ($charge->isSuccessful()) {
                $this->paymentSuccessful($order, $invoice, $payment, $charge->id);
                return $this->redirect(self::PATH_SUCCESS);
            }

            $this->paymentPending($order, $payment);

            // TODO: Should redirect users to a page that tell users that
            //       their payment is in review instead of success page.
            return $this->redirect(self::PATH_SUCCESS);

        } catch (Exception $e) {
            $this->invalid($e->getMessage(), $order);
            return $this->redirectToCart();
        }
    }

    /**
     * @return \Magento\Sales\Model\Order $order
     */
    protected function tryLoadOrder()
    {
        $order = $this->session->getLastRealOrder();

        return $order->getId()
            ? $order
            : throw new Exception(__('The order session no longer exists, please make an order again or contact our support if you have any questions.'));
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return \Magento\Sales\Api\Data\InvoiceInterface
     */
    protected function tryLoadInvoice($order)
    {
        return $order->hasInvoices()
            ? $order->getInvoiceCollection()->getLastItem()
            : throw new Exception(__('Cannot create an invoice. Please contact our support to confirm your payment.'));
    }

    /**
     * @param  \Magento\Sales\Model\Order $order
     *
     * @return \Magento\Sales\Model\Order\Payment
     */
    protected function tryLoadPayment($order)
    {
        if (! $payment = $order->getPayment()) {
            throw new Exception(__('Cannot retrieve a payment detail from the request. Please contact our support if you have any questions.'));
        }

        if ($payment->getMethod() != $this->getPaymentMethodCode()) {
            throw new Exception(__('Invalid payment method. Please contact our support if you have any questions.'));
        }

        return $payment;
    }

    /**
     * @param  \Magento\Sales\Model\Order\Payment $payment
     *
     * @return string
     */
    protected function tryLoadChargeId($payment)
    {
        $charge = $this->charge->find($charge_id);

        if (! $charge instanceof \Omise\Payment\Model\Api\BaseObject) {
            throw new Exception(__('Couldn\'t retrieve charge transaction. Please contact administrator.'));
        }

        if ($charge instanceof \Omise\Payment\Model\Api\Error) {
            throw new Exception($charge->getMessage());
        }

        return $charge;
    }

    /**
     * @param string $id
     *
     * @return \Omise\Payment\Model\Api\Charge
     */
    protected function tryLoadCharge($id)
    {
        if ($chargeId = $payment->getAdditionalInformation('charge_id')) {
            return $chargeId;
        }

        throw new Exception(__('Cannot retrieve a charge reference id. Please contact our support to confirm your payment.'));
    }

    /**
     * @param \Magento\Framework\Phrase|string $message
     * @param \Magento\Sales\Model\Order       $order
     */
    protected function invalid($message, Order $order = null)
    {
        $this->messageManager->addErrorMessage($message);

        if ($order) {
            $order->addStatusHistoryComment($message);
            $order->save();    
        }
    }

    protected function paymentPending(&$order, &$payment)
    {
        $order->setState(Order::STATE_PAYMENT_REVIEW);
        $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PAYMENT_REVIEW));

        $transaction = $payment->addTransaction(Transaction::TYPE_PAYMENT);
        $transaction->setIsClosed(false);
        $payment->addTransactionCommentsToOrder(
            $transaction,
            __('The payment has been processing.<br/>Due to the Bank process, this might takes a few seconds or up-to an hour. Please click "Accept" or "Deny" the payment manually once the result has been updated (you can check at Omise Dashboard).')
        );

        $order->save();
    }

    protected function paymentSuccessful(&$order, &$invoice, &$payment, $transaction_id)
    {
        // Update order state and status.
        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));

        $invoice->setTransactionId($transaction_id)->pay()->save();

        $payment->addTransactionCommentsToOrder(
            $payment->addTransaction(Transaction::TYPE_PAYMENT, $invoice),
            __(
                'Amount of %1 has been paid via Omise' . $this->getPaymentMethodTitle() . 'payment',
                $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal())
            )
        );

        $order->save();
    }

    protected function paymentFailed(&$order, &$invoice, $note = null)
    {
        $invoice->cancel();
        $order->addRelatedObject($invoice);
        $order->registerCancellation($note)->save();

        if ($note) {
            $this->messageManager->addErrorMessage($note);
        }
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
     * @return \Magento\Framework\App\ResponseInterface
     */
    protected function redirectToCart()
    {
        return $this->redirect(self::PATH_CART);
    }
}
