<?php
namespace Omise\Payment\Controller\Callback;

use Exception;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Omise\Payment\Model\Omise;
use Omise\Payment\Model\Api\Charge;
use Magento\Checkout\Model\Session as CheckoutSession;
use Omise\Payment\Model\Api\CheckoutSession as OmiseCheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Sales\Model\Order;
use Magento\Framework\Exception\LocalizedException;

class UPACallback extends Action
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

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var OmiseCheckoutSession
     */
    protected $omiseCheckoutSession;

    /**
     * @param Context $context
     * @param Session $session
     * @param Omise   $omise
     * @param Charge  $charge
     * @param CheckoutSession $checkoutSession
     * @param OmiseCheckoutSession $omiseCheckoutSession
     */
    public function __construct(
        Context $context,
        Session $session,
        Omise   $omise,
        Charge  $charge,
        CheckoutSession $checkoutSession,
        OmiseCheckoutSession $omiseCheckoutSession
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->omise   = $omise;
        $this->charge  = $charge;
        $this->checkoutSession  = $checkoutSession;
        $this->omiseCheckoutSession = $omiseCheckoutSession;
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

        if (!$this->isValid($order)) {
            return $this->redirect(self::PATH_CART);
        }

        $orderState = $order->getState();
        if ($orderState === Order::STATE_PROCESSING) {
            return $this->redirect(self::PATH_SUCCESS);
        }

        try {
            $payment = $order->getPayment();
            $checkoutSession = $this->getCheckoutSession($payment);
            $sessionPayments = $checkoutSession->payments;

            if ($checkoutSession && !is_array($sessionPayments) || empty($sessionPayments)) {
                $errorMessage = __('The payment session is invalid or no payment information was found. Please contact our support if you have any questions.');
                return $this->redirectBackToCart($order, $errorMessage);
            }

            $sessionStatus = $checkoutSession->status;
            $finalPayment = $this->getFinalPayment($sessionPayments, $sessionStatus);

            if (!empty($finalPayment) && !empty($finalPayment['charge_id'])) {
                $chargeId = $finalPayment['charge_id'];
                $charge = $this->charge->find($chargeId);
            } else {
                $errorMessage = __('The payment session is invalid or no payment information was found. Please contact our support if you have any questions.');
                return $this->redirectBackToCart($order, $errorMessage);
            }

            if (!$charge instanceof \Omise\Payment\Model\Api\BaseObject) {
                throw new LocalizedException(
                    __('Couldn\'t retrieve charge transaction. Please contact administrator.')
                );
            }
            if ($charge instanceof \Omise\Payment\Model\Api\Error) {
                // restoring the cart
                $this->checkoutSession->restoreQuote();
                throw new LocalizedException(__($charge->getMessage()));
            }
            if ($charge->isFailed()) {
                $this->handleFailure($charge);
            }
            return $this->redirect(self::PATH_SUCCESS);
        } catch (Exception $e) {
            $this->cancel($order, $e->getMessage());
            return $this->redirect(self::PATH_CART);
        }
    }

    /**
     * Mark order as failed
     *
     * @param object $charge
     */
    private function handleFailure($charge)
    {
        // restoring the cart
        $this->checkoutSession->restoreQuote();
        $failureMessage = $charge->failure_message ?
            ucfirst($charge->failure_message) :
            __('Payment cancelled');
        $errorMessage = __(
            'Payment failed. %1, please contact our support if you have any questions.',
            $failureMessage
        );

        // This cancels the order, logs error and displays message in cart page
        throw new \Magento\Framework\Exception\LocalizedException($errorMessage);
    }

    /**
     * Check if the transaction is valid
     *
     * @param object $order
     * @return boolean
     */
    private function isValid($order)
    {
        if (!$order->getId()) {
            $this->messageManager->addErrorMessage(__('The order session no longer exists, please make an order
            again or contact our support if you have any questions.'));

            return false;
        }

        $payment = $order->getPayment();

        if (!$payment) {
            $this->invalid($order, __('Cannot retrieve a payment detail from the request. Please contact our
            support if you have any questions.'));

            return false;
        }

        $orderState = $order->getState();
        $validOrderStates = [Order::STATE_CANCELED, Order::STATE_PENDING_PAYMENT, Order::STATE_PAYMENT_REVIEW, Order::STATE_PROCESSING];
        
        if (!in_array($orderState, $validOrderStates)) {
            $this->invalid($order, __('Invalid order status, cannot validate the payment. Please contact our
            support if you have any questions.'));

            return false;
        }

        return true;
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
        if ($order->hasInvoices()) {
            $invoice = $this->invoice($order);
            $invoice->cancel();
            $order->addRelatedObject($invoice);
        }

        $order->registerCancellation($message)->save();
        $this->messageManager->addErrorMessage($message);
    }

    private function redirectBackToCart($order, $errorMessage)
    {
        $this->invalid($order, $errorMessage);
        $this->checkoutSession->restoreQuote();
        return $this->redirect(self::PATH_CART);
    }

    /**
     * @param Magento\Sales\Model\Order\Payment $payment
     * @return \Omise\Payment\Model\Api\CheckoutSession|null
     */
    private function getCheckoutSession($payment)
    {
        $sessionId = $payment->getAdditionalInformation('session_id');
        if (empty($sessionId)) {
            $this->checkoutSession->restoreQuote();
            throw new LocalizedException(
                __('Cannot retrieve a session reference id. Please contact our support to confirm your payment.')
            );
        }

        $checkoutSession = $this->omiseCheckoutSession->getSessionInfo($sessionId);
        return $checkoutSession;
    }

    /**
     * @param array $payments
     * @param string $sessionStatus
     * @return array|null
     */
    private function getFinalPayment($payments, $sessionStatus)
    {
        if (!is_array($payments) || empty($payments)) {
            return null;
        }

        $paymentsWithChargeId = array_filter($payments, function ($payment) {
            return !empty($payment['charge_id']);
        });

        if (empty($paymentsWithChargeId)) {
            return null;
        }

        foreach ($paymentsWithChargeId as $payment) {
            $paymentStatus = strtolower(isset($payment['status']) ? (string) $payment['status'] : '');
            if ($paymentStatus === strtolower((string) $sessionStatus)) {
                return $payment;
            }
        }

        return end($paymentsWithChargeId);
    }
}
