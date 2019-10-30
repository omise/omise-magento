<?php
namespace Omise\Payment\Model;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\PaymentException;
use Magento\Framework\Exception\SessionException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Omise\Payment\Api\Data\PaymentInterface;
use Omise\Payment\Api\Data\PaymentInterfaceFactory;
use Omise\Payment\Api\PaymentInformationInterface;

class PaymentInformation implements PaymentInformationInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * @var \Omise\Payment\Api\Data\PaymentInterfaceFactory
     */
    private $data_factory;

    /**
     * @var Magento\Sales\Api\OrderRepositoryInterface
     */
    private $_order_repository;

    public function __construct(Session $session, PaymentInterfaceFactory $data_factory, OrderRepositoryInterface $order_repository)
    {
        $this->session           = $session;
        $this->data_factory      = $data_factory;
        $this->_order_repository = $order_repository;
    }

    /**
     * @param  int $id
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function loadOrder($id)
    {
        // Note, $order->getId(); will return a string, not int.
        $order = $this->session->getLastRealOrder();

        if (!$order->getId()) {
            throw new SessionException(__('The order session no longer exists, please make an order again or contact our support if you have any questions.'));
        }

        if ($id != $order->getId()) {
            throw new AuthorizationException(__('This request is not authorized to access the resource, please contact our support if you have any questions'));
        }

        return $order;
    }

    /**
     * @param  int $order_id
     *
     * @return Omise\Payment\Api\Data\PaymentInterface
     */
    public function offsite($order_id)
    {
        if ($payment = $this->loadOrder($order_id)->getPayment()) {
            $data = $this->data_factory->create();
            $data->setOrderId($order_id);
            $data->setAuthorizeUri($payment->getAdditionalInformation('charge_authorize_uri'));

            return $data;
        }

        throw new PaymentException(__('Cannot retrieve a payment detail from the request, please contact our support if you have any questions'));
    }

    /**
     * @param  int $order_id
     *
     * @return Omise\Payment\Api\Data\PaymentInterface
     */
    public function paymentInfo($order_id)
    {
        $order = $this->_order_repository->get(66);

        if (!$order) {
            return false;
        }

        return $order->getPayment()->getState() === \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW;
    }
}
