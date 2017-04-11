<?php
namespace Omise\Payment\Controller\Callback;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment\Transaction;
use Omise\Payment\Gateway\Validator\Message\Invalid;
use Omise\Payment\Model\Config\Offsite\Internetbanking as Config;
use Omise\Payment\Model\Validator\Payment\CaptureResultValidator;

class Internetbanking extends Action
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
     * @var \Omise\Payment\Model\Config\Offsite\Internetbanking
     */
    protected $config;

    public function __construct(
        Context $context,
        Session $session,
        Config  $config
    ) {
        parent::__construct($context);

        $this->session = $session;
        $this->config  = $config;
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

        if ($payment->getMethod() !== 'omise_offsite_internetbanking') {
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
            $charge = \OmiseCharge::retrieve($charge_id, $this->config->getPublicKey(), $this->config->getSecretKey());

            $result = $this->validate($charge);

            if ($result instanceof Invalid) {
                throw new Exception($result->getMessage());
            }

            $payment->setTransactionId($charge['transaction']);
            $payment->setLastTransId($charge['transaction']);

            // Update order state and status.
            $order->setState(Order::STATE_PROCESSING);
            $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));

            $invoice = $this->invoice($order);
            $invoice->setTransactionId($charge['transaction'])->pay()->save();

            // Add transaction.
            $payment->addTransactionCommentsToOrder(
                $payment->addTransaction(Transaction::TYPE_PAYMENT, $invoice),
                __(
                    'Amount of %1 has been paid via Omise Internet Banking payment',
                    $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal())
                )
            );

            $order->save();
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
     * @param  \OmiseCharge $charge
     *
     * @return bool|Omise\Payment\Gateway\Validator\Message\Invalid
     */
    protected function validate($charge)
    {
        return (new CaptureResultValidator)->validate($charge);
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
