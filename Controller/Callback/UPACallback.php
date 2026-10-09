<?php
namespace Omise\Payment\Controller\Callback;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Omise\Payment\Model\Omise;
use Magento\Framework\App\Action\Action;
use Magento\Sales\Model\Order;

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
     * @param Context $context
     * @param Session $session
     * @param Omise   $omise
     */
    public function __construct(
        Context $context,
        Session $session,
        Omise   $omise
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->omise   = $omise;
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
        $validOrderStates = [Order::STATE_PENDING_PAYMENT, Order::STATE_PAYMENT_REVIEW, Order::STATE_PROCESSING];
        
        if (!in_array($orderState, $validOrderStates)) {
            $this->invalid($order, __('Invalid order status, cannot validate the payment. Please contact our
            support if you have any questions.'));

            return false;
        }

        return true;
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
}
