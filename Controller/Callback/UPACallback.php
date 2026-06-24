<?php
namespace Omise\Payment\Controller\Callback;

use Exception;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Omise\Payment\Model\Omise;
use Omise\Payment\Model\Api\Charge;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Helper\OmiseEmailHelper;
use Omise\Payment\Model\Config\Cc as Config;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Request\Http;
use Omise\Payment\Model\Api\CheckoutSession as OmiseCheckoutSession;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface as TransactionBuilderInterface;
use Magento\Framework\App\Action\Action;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction;
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
     * @var \Omise\Payment\Helper\OmiseHelper
     */
    protected $helper;

    /**
     * @var \Omise\Payment\Helper\OmiseEmailHelper
     */
    protected $emailHelper;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var OmiseCheckoutSession
     */
    protected $omiseCheckoutSession;

    /**
     * @var TransactionBuilderInterface
     */
    protected $transactionBuilder;

    /**
     * @param Context $context
     * @param Session $session
     * @param Omise   $omise
     * @param Charge  $charge
     * @param OmiseHelper $helper
     * @param OmiseEmailHelper $emailHelper
     * @param Config $config
     * @param CheckoutSession $checkoutSession
     * @param Http $request
     * @param OmiseCheckoutSession $omiseCheckoutSession
     * @param TransactionBuilderInterface $transactionBuilder
     */
    public function __construct(
        Context $context,
        Session $session,
        Omise   $omise,
        Charge  $charge,
        OmiseHelper $helper,
        OmiseEmailHelper $emailHelper,
        Config $config,
        CheckoutSession $checkoutSession,
        Http $request,
        OmiseCheckoutSession $omiseCheckoutSession,
        TransactionBuilderInterface $transactionBuilder
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->omise   = $omise;
        $this->charge  = $charge;
        $this->helper  = $helper;
        $this->emailHelper = $emailHelper;
        $this->config = $config;
        $this->checkoutSession  = $checkoutSession;
        $this->request = $request;
        $this->omiseCheckoutSession = $omiseCheckoutSession;
        $this->transactionBuilder = $transactionBuilder;
        $this->omise->defineUserAgent();
        $this->omise->defineApiVersion();
        $this->omise->defineApiKeys();
    }

    /**
     * @var Magento\Sales\Model\Order\Payment
     * @var string
     * @return bool
     */
    private function validateChargeId($payment, $chargeId)
    {
        try {
            $sessionId = $payment->getAdditionalInformation('session_id');
            $checkoutSessionInfo = $this->omiseCheckoutSession->getSessionInfo($sessionId);

            if (!is_array($checkoutSessionInfo->payments)) {
                return false;
            }
            
            foreach ($checkoutSessionInfo->payments as $charge) {
                if ($charge['charge_id'] === $chargeId) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $order = $this->session->getLastRealOrder();
        $chargeId = $this->getRequest()->getParam('chargeId');

        if (!$this->isValid($order)) {
            return $this->redirect(self::PATH_CART);
        }

        $orderState = $order->getState();
        if ($orderState === Order::STATE_PROCESSING) {
            return $this->redirect(self::PATH_SUCCESS);
        }

        try {
            $payment = $order->getPayment();
            $charge = $this->charge->find($chargeId);
            if (! $charge instanceof \Omise\Payment\Model\Api\BaseObject) {
                throw new LocalizedException(
                    __('Couldn\'t retrieve charge transaction. Please contact administrator.')
                );
            }
            if ($charge instanceof \Omise\Payment\Model\Api\Error) {
                // restoring the cart
                $this->checkoutSession->restoreQuote();
                throw new LocalizedException(__($charge->getMessage()));
            }
            $paymentMethod = $payment->getMethod();

            if ($charge->isFailed()) {
                return $this->handleFailure($charge);
            }

            $payment->setTransactionId($charge->id);
            $payment->setLastTransId($charge->id);
            
            // Do not proceed if webhook is enabled
            if ($this->config->isWebhookEnabled()) {
                $transaction = $this->transactionBuilder
                    ->setPayment($payment)
                    ->setOrder($order)
                    ->setTransactionId($charge->id)
                    ->setAdditionalInformation([
                        Transaction::RAW_DETAILS => [
                            'omise_charge_id' => $charge->id,
                            'status' => $charge->status
                        ]
                    ])
                    ->setFailSafe(true)
                    ->build(Transaction::TYPE_PAYMENT);

                $payment->addTransactionCommentsToOrder(
                    $transaction,
                    __(
                        'Omise payment captured successfully.'
                    )
                );
                $order->save();
                return $this->redirect(self::PATH_SUCCESS);
            }

            if ($charge->isSuccessful()) {
                return $this->handleSuccess($order, $charge->id, $payment, $paymentMethod);
            }

            $this->handlePending($order, $payment);
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
     * Mark order as success
     *
     * @param object $order
     * @param string $chargeId
     * @param object $payment
     * @param string $paymentMethod
     */
    private function handleSuccess($order, $chargeId, $payment, $paymentMethod)
    {
        // Update order state and status.
        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));

        $invoice = $this->helper->createInvoiceAndMarkAsPaid($order, $chargeId);
        $this->emailHelper->sendInvoiceAndConfirmationEmails($order);

        $paymentMethodLabel = $this->helper->getOmiseLabelByOmiseCode($paymentMethod);
        // Add transaction.
        $payment->addTransactionCommentsToOrder(
            $payment->addTransaction(Transaction::TYPE_CAPTURE, $invoice),
            __(
                "Amount of %1 has been paid via Omise %2 payment",
                $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()),
                $paymentMethodLabel
            )
        );

        $order->save();
        return $this->redirect(self::PATH_SUCCESS);
    }

    /**
     * Mark order as pending
     *
     * @param object $order
     * @param object $payment
     */
    private function handlePending($order, $payment)
    {
        // Update order state and status.
        $order->setState(Order::STATE_PAYMENT_REVIEW);
        $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PAYMENT_REVIEW));

        // Add transaction.
        $transaction = $payment->addTransaction(Transaction::TYPE_PAYMENT);
        $transaction->setIsClosed(false);
        $payment->addTransactionCommentsToOrder(
            $transaction,
            __('The payment is under processing.<br/>Due to bank processing, this might take up to an hour to
             complete. The payment status will be updated once the processing result is available (you can
             check the latest status on the Omise Dashboard).')
        );

        $order->save();

        // TODO: Should redirect users to a page that tell users that
        // their payment is in review instead of success page.
        return $this->redirect(self::PATH_SUCCESS);
    }

    /**
     * Check if the transaction is valid
     *
     * @param object $order
     * @return boolean
     */
    private function isValid($order)
    {
        if (! $order->getId()) {
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

        $chargeId = $this->request->getParam('chargeId');
        $isValidCharge = $this->validateChargeId($payment, $chargeId);

        if (!$chargeId || !$isValidCharge) {
            $this->invalid(
                $order,
                __('The URL is invalid. Please contact our support if you have any questions.')
            );

            return false;
        }

        $orderState = $order->getState();
        $validOrderStates = [Order::STATE_PENDING_PAYMENT, Order::STATE_PROCESSING];
        
        if (!in_array($orderState, $validOrderStates)) {
            $this->invalid($order, __('Invalid order status, cannot validate the payment. Please contact our
            support if you have any questions.'));

            return false;
        }

        if (!$payment->getAdditionalInformation('session_id')) {
            $this->cancel(
                $order,
                __('Cannot retrieve a session reference id. Please contact our support to confirm your payment.')
            );
            $this->session->restoreQuote();

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
}
