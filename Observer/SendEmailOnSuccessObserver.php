<?php
namespace Omise\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class SendEmailOnSuccessObserver implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderModel;

    /**
     * @var \Omise\Payment\Helper\OmiseEmailHelper
     */
    protected $_emailHelper;

    /**
     * @var \Omise\Payment\Helper\OmiseHelper
     */
    protected $_helper;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderModel
     * @param \Magento\Sales\Model\OmiseEmailHelper $emailHelper
     * @param \Omise\Payment\Helper\OmiseHelper $helper
     *
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderModel,
        \Omise\Payment\Helper\OmiseEmailHelper $emailHelper,
        \Omise\Payment\Helper\OmiseHelper $helper
    ) {
        $this->orderModel = $orderModel;
        $this->_emailHelper = $emailHelper;
        $this->_helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        $order = $this->orderModel->create()->load($orderIds[0]);

        $paymentMethod = $order->getPayment()->getMethod();

        if ($this->_helper->isOffsitePayment($paymentMethod)) {
            $this->_emailHelper->sendInvoiceAndConfirmationEmails($orderIds, $order);
        }
    }
}
