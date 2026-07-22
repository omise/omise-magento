<?php
namespace Omise\Payment\Model;

use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Omise\Payment\Helper\OmiseHelper;
use Magento\Checkout\Model\Session;
use Omise\Payment\Model\Config\Installment;
use Omise\Payment\Observer\InstallmentDataAssignObserver;

class DynamicCommandPool implements CommandPoolInterface
{
    /**
     * @var CommandPoolInterface
     */
    private $apmPool;

    /**
     * @var CommandPoolInterface
     */
    private $upaPool;
    
    /**
     * @var OmiseHelper
     */
    private $omiseHelper;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * DynamicCommandPool constructor.
     * @param CommandPoolInterface $apmPool
     * @param CommandPoolInterface $upaPool
     * @param OmiseHelper $omiseHelper
     * @param Session $checkoutSession
     */
    public function __construct(
        CommandPoolInterface $apmPool,
        CommandPoolInterface $upaPool,
        OmiseHelper $omiseHelper,
        Session $checkoutSession
    ) {
        $this->apmPool = $apmPool;
        $this->upaPool = $upaPool;
        $this->omiseHelper = $omiseHelper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param string $commandCode
     * @return \Magento\Payment\Gateway\Command\CommandInterface
     */
    public function get($commandCode)
    {
        $quote = $this->checkoutSession->getQuote();
        $methodCode = null;
        $isWlbInstallment = false;

        if ($quote && $quote->getPayment()) {
            $methodCode = $quote->getPayment()->getMethod();
        }

        $isWlbInstallment = $methodCode == Installment::CODE && $quote->getPayment() && 
            $quote->getPayment()->getAdditionalInformation(InstallmentDataAssignObserver::WLB) == true;
        if (!empty($methodCode) && $this->omiseHelper->isAllowUpa($methodCode) && !$isWlbInstallment) {
            return $this->upaPool->get($commandCode);
        }
        return $this->apmPool->get($commandCode);
    }
}
