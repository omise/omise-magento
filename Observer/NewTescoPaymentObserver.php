<?php
namespace Omise\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;

class NewTescoPaymentObserver implements ObserverInterface
{
    private $log;
    public function __construct(
        \PSR\Log\LoggerInterface $log
    ) {
        $this->log = $log;
    }
    /**
     * Set forced canCreditmemo flag
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getPayment()->getAdditionalData();

        $this->log->debug('observer', ['vars'=>(($order))/*->getPayment()->getMethodInstance()->getCode()*/]);

        return $this;
    }
}
