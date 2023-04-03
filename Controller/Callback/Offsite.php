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
use Magento\Framework\Exception\LocalizedException;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Helper\OmiseEmailHelper;
use Omise\Payment\Model\Config\Cc as Config;
use Magento\Checkout\Model\Session as CheckoutSession;
use Psr\Log\LoggerInterface;
use Omise\Payment\Controller\Callback\Traits\FailedChargeTrait;
use Magento\Framework\App\Request\Http;

class Offsite extends Action
{
    use FailedChargeTrait;

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

    public function __construct(
        Context $context,
        Session $session,
        Omise   $omise,
        Charge  $charge,
        OmiseHelper $helper,
        OmiseEmailHelper $emailHelper,
        Config $config,
        CheckoutSession $checkoutSession,
        LoggerInterface $logger,
        Http $request
    ) {
        parent::__construct($context);

        $this->session = $session;
        $this->omise   = $omise;
        $this->charge  = $charge;
        $this->helper  = $helper;
        $this->emailHelper = $emailHelper;
        $this->config = $config;
        $this->checkoutSession  = $checkoutSession;
        $this->logger = $logger;
        $this->request = $request;

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
            $chargeId = $payment->getAdditionalInformation('charge_id');
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
            $shopeePayFailed = $this->helper->hasShopeepayFailed($paymentMethod, $charge->isSuccessful());

            if ($charge->isFailed() || $shopeePayFailed) {
                return $this->handleFailure($charge, $shopeePayFailed);
            }

            // Do not proceed if webhook is enabled
            if ($this->config->isWebhookEnabled()) {
                return $this->redirect(self::PATH_SUCCESS);
            }

            $payment->setTransactionId($charge->id);
            $payment->setLastTransId($charge->id);

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
     * @param boolean $shopeePayFailed {TODO: Remove this once backend issue is fixed}
     */
    private function handleFailure($charge, $shopeePayFailed)
    {
        // restoring the cart
        $this->checkoutSession->restoreQuote();
        $failureMessage = $charge->failure_message ?
            ucfirst($charge->failure_message) :
            'Payment cancelled';
        $errorMessage = __(
            "Payment failed. $failureMessage, please contact our support if you have any questions."
        );

        // pass shopeePayFailed to avoid webhook to cancel payment
        return $this->processFailedCharge($errorMessage, $shopeePayFailed);
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
            $payment->addTransaction(Transaction::TYPE_PAYMENT, $invoice),
            __(
                "Amount of %1 has been paid via Opn Payments $paymentMethodLabel payment",
                $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal())
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
            __('The payment is under processing.<br/>Due to Bank process, this might take up to an hour to
            complete. Click "Accept" or "Deny" to accept or deny the payment manually, after the result of
            the processing has been updated (check the result on the Opn Payments Dashboard).')
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

        $token = $this->request->getParam('token');

        if (!$token || $payment->getAdditionalInformation('token') !== rtrim($token, "/")) {
            $this->invalid(
                $order,
                __('The URL is invalid. Please contact our support if you have any questions.')
            );

            return false;
        }

        $orderState = $order->getState();
        $validOrderStates = [Order::STATE_PENDING_PAYMENT, Order::STATE_CANCELED, Order::STATE_PROCESSING];

        if (!in_array($orderState, $validOrderStates)) {
            $this->invalid($order, __('Invalid order status, cannot validate the payment. Please contact our
            support if you have any questions.'));

            return false;
        }

        $paymentMethod = $payment->getMethod();

        if (!$this->helper->isOffsitePaymentMethod($paymentMethod)) {
            $this->invalid(
                $order,
                __('Invalid payment method. Please contact our support if you have any questions.')
            );
            return false;
        }

        if (!$payment->getAdditionalInformation('charge_id')) {
            $this->cancel(
                $order,
                __('Cannot retrieve a charge reference id. Please contact our support to confirm your payment.')
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
