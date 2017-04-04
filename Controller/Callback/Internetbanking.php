<?php
namespace Omise\Payment\Controller\Callback;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
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
        $resultRedirect = $this->resultRedirectFactory->create();
        $order          = $this->session->getLastRealOrder();

        if (! $order->getId()) {
            $this->invalid($order, 'The order session no longer exists, please make an order again or contact our support if you have any questions.');

            return $this->redirect(self::PATH_CART);
        }

        if (! $order->isPaymentReview()) {
            $this->invalid($order, 'Invalid order status, cannot validate the payment. Please contact our support if you have any questions.');

            return $this->redirect(self::PATH_CART);
        }

        if (! $payment = $order->getPayment()) {
            $this->invalid($order, 'Cannot retrieve a payment detail from the request. Please contact our support if you have any questions.');

            return $this->redirect(self::PATH_CART);
        }

        if ($payment->getMethod() !== 'omise_offsite_internetbanking') {
            $this->cancel($order, 'Invalid payment method. Please contact our support if you have any questions.');
            $this->session->restoreQuote();

            return $this->redirect(self::PATH_CART);
        }

        if (! $charge_id = $payment->getAdditionalInformation('charge_id')) {
            $this->cancel($order, 'Cannot retrieve a charge reference id. Please contact our support if you have any questions.');
            $this->session->restoreQuote();

            return $this->redirect(self::PATH_CART);
        }

        try {
            $charge = \OmiseCharge::retrieve($charge_id, $this->config->getPublicKey(), $this->config->getSecretKey());

            if (! $this->validate($charge)) {
                throw new Exception('Payment failed, ' . $charge['failure_message'] . ' ( ' . $charge['failure_code'] . ' ). Please contact our support if you have any questions.');
            }

            // TODO: Update order status to success payment.
            $payment->accept();

            return $this->redirect(self::PATH_SUCCESS);
        } catch (Exception $e) {
            $this->cancel($order, $e->getMessage());
            $this->session->restoreQuote();

            return $this->redirect(self::PATH_CART);
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
     * @param  \OmiseCharge
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
     * @param \Magento\Sales\Model\Order
     */
    protected function cancel(Order $order, $message)
    {
        $this->invalid($order, $message);

        $order->setStatus(Order::STATE_CANCELED);
        $order->save();
    }

    /**
     * @param \Magento\Sales\Model\Order
     * @param string
     */
    protected function invalid(Order $order, $message)
    {
        $order->addStatusHistoryComment(__($message));

        $this->messageManager->addErrorMessage(__($message));
    }
}
