<?php
namespace Omise\Payment\Controller\Callback;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction;
use Omise\Payment\Gateway\Validator\Message\Invalid;
use Omise\Payment\Model\Config\Cc as Config;
use Omise\Payment\Model\Validator\Payment\AuthorizeResultValidator;
use Omise\Payment\Model\Validator\Payment\CaptureResultValidator;

class Threedsecure extends Action
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
     * @var \Omise\Payment\Model\Config\Cc
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

        if (! $payment = $order->getPayment()) {
            $this->invalid($order, __('Cannot retrieve a payment detail from the request. Please contact our support if you have any questions.'));

            return $this->redirect(self::PATH_CART);
        }

        if ($payment->getMethod() !== 'omise' && $payment->getMethod() !== 'omise_cc') {
            $this->invalid($order, __('Invalid payment method. Please contact our support if you have any questions.'));

            return $this->redirect(self::PATH_CART);
        }

        if (! $charge_id = $payment->getAdditionalInformation('charge_id')) {
            $this->cancel($order, __('Cannot retrieve a charge reference id. Please contact our support to confirm your payment.'));
            $this->session->restoreQuote();

            return $this->redirect(self::PATH_CART);
        }

        if ($order->getState() !== Order::STATE_PENDING_PAYMENT) {
            if ($order->isCanceled()) {
                $this->messageManager->addErrorMessage(__('This order has been canceled. Please contact our support if you have any questions.'));
                return $this->redirect(self::PATH_CART);
            }

            return $this->redirect(self::PATH_SUCCESS);
        }

        try {
            $charge = \OmiseCharge::retrieve($charge_id, $this->config->getPublicKey(), $this->config->getSecretKey());

            $result = $this->validate($charge);

            if ($result instanceof Invalid) {
                throw new Exception($result->getMessage());
            }

            $order->setState(Order::STATE_PROCESSING);
            $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));

            // Update order state and status.
            if ($order->hasInvoices()) {
                $payment->setTransactionId($charge['transaction']);
                $payment->setLastTransId($charge['transaction']);

                $invoice = $this->invoice($order);
                $invoice->setTransactionId($charge['transaction'])->pay()->save();

                // Add transaction.
                $payment->addTransactionCommentsToOrder(
                    $payment->addTransaction(Transaction::TYPE_CAPTURE, $invoice),
                    __(
                        'Captured amount of %1 online via Omise Payment Gateway (3-D Secure payment).',
                        $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal())
                    )
                );
            } else {
                $payment->addTransactionCommentsToOrder(
                    $payment->addTransaction(Transaction::TYPE_AUTH),
                    $payment->prependMessage(
                        __(
                            'Authorized amount of %1 via Omise Payment Gateway (3-D Secure payment).',
                            $order->getBaseCurrency()->formatTxt($order->getTotalDue())
                        )
                    )
                );
            }

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
        if ($charge['capture']) {
            return (new CaptureResultValidator)->validate($charge);
        }

        return (new AuthorizeResultValidator)->validate($charge);
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
