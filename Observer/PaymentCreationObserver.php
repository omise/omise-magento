<?php
namespace Omise\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class PaymentCreationObserver implements ObserverInterface
{
    /**
     * @var \Omise\Payment\Helper\OmiseHelper
     */
    private $_helper;

    /**
     * @var \Omise\Payment\Model\Data\Email
     */
    private $_email;

    /**
     * @param \Omise\Payment\Helper\OmiseHelper $helper
     */
    public function __construct(
        \Omise\Payment\Helper\OmiseHelper $helper,
        \Omise\Payment\Model\Data\Email $email
    ) {
        $this->_helper           = $helper;
        $this->_email           = $email;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $order   = $observer->getEvent()->getOrder();
        $paymentMethod = $order->getPayment()->getMethod();

        if ($this->_helper->isOfflineOrOffsite($paymentMethod)) {
            $order->setCanSendNewEmailFlag(false);
        }

        // Offline QR code payment emails
        if ($this->_helper->isPayableByImageCode($paymentMethod)) {
            $this->_email->sendEmail($order);
        }
        return $this;
    }
}
