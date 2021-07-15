<?php
namespace Omise\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class FpxSendEmailOnSuccessObserver implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
    */
    protected $orderModel;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
    */
    protected $orderSender;

    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
    */
    protected $checkoutSession;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderModel
    * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    * @param \Magento\Checkout\Model\Session $checkoutSession
    *
    */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderModel,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->orderModel = $orderModel;
        $this->orderSender = $orderSender;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
    * @return void
    */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // if fpx
        $orderIds = $observer->getEvent()->getOrderIds();
        if(count($orderIds))
        {
            $this->checkoutSession->setForceOrderMailSentOnSuccess(true);
            $order = $this->orderModel->create()->load($orderIds[0]);
            $this->orderSender->send($order, true);
        }
    }
}
