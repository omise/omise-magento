<?php
namespace Omise\Payment\Controller\Callback;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment\Transaction;
use Omise\Payment\Model\Config\Offsite\Internetbanking as Config;

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

            if (! $this->validate($charge)) {
                throw new Exception('Payment failed. ' . ucfirst($charge['failure_message']) . ', please contact our support if you have any questions.');
            }

            $payment->setTransactionId($charge['transaction']);
            $payment->setLastTransId($charge['transaction']);

            // Update order state and status.
            $order->setState(Order::STATE_PROCESSING);
            $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));

            // OmiseMagento doesn't support for partial-capture.
            // Thus, STATE_OPEN invoice should always has only one in an order.
            $invoice = null;
            foreach ($order->getInvoiceCollection() as $item) {
                if ($item->getState() == Invoice::STATE_OPEN) {
                    $invoice = $item;
                }
            }

            if ($invoice) {
                $invoice->setTransactionId($charge['transaction'])
                    ->pay()
                    ->save();

                // Add transaction.
                $payment->addTransactionCommentsToOrder(
                    $payment->addTransaction(Transaction::TYPE_PAYMENT, $invoice),
                    __(
                        'Amount of %1 has been paid via Omise Internet Banking payment',
                        $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal())
                    )
                );
            } else {
                $order->addStatusHistoryComment(
                    __(
                        'Amount of %1 has been paid via Omise Internet Banking payment, but cannot retrieve a related invoice. Please confirm the payment.',
                        $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal())
                    )
                );
            }

            $order->save();
            return $this->redirect(self::PATH_SUCCESS);
        } catch (Exception $e) {
            $this->cancel($order, $e->getMessage());
            $this->session->restoreQuote();

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
     * @return bool
     */
    protected function validate($charge)
    {
        $captured = $charge['captured'] ? $charge['captured'] : $charge['paid'];

        if ($charge['status'] === 'successful'
            && $charge['authorized'] == true
            && $captured == true
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param string                     $message
     */
    protected function cancel(Order $order, $message)
    {
        $this->invoice($order)->cancel()->save();

        $order->setState(Order::STATE_CANCELED);
        $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CANCELED));

        $this->invalid($order, $message);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param string                     $message
     */
    protected function invalid(Order $order, $message)
    {
        $order->addStatusHistoryComment(__($message));
        $order->save();

        $this->messageManager->addErrorMessage(__($message));
    }
}
