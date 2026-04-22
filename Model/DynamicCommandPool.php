<?php 
namespace Omise\Payment\Model;

use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Omise\Payment\Helper\OmiseHelper;
use Magento\Checkout\Model\Session;
use Omise\Payment\Model\Config\Installment;

class DynamicCommandPool implements CommandPoolInterface
{
    private $apmPool;
    private $upaPool;
    private $omiseHelper;
    private $checkoutSession;

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

    public function get($commandCode)
    {
        $quote = $this->checkoutSession->getQuote();
        $methodCode = null;
        $isInstallment = $wlb = 0;

        if ($quote && $quote->getPayment()) {
            $methodCode = $quote->getPayment()->getMethod();
            $payment = $quote->getPayment();
            if($methodCode == Installment::CODE){
                $isInstallment = 1;
                $wlb = $payment->getAdditionalInformation('wlb');
            }
            
        }
        if($isInstallment){
            if($this->omiseHelper->isAllowUpa($methodCode) && !$wlb){
                return $this->upaPool->get($commandCode);
            }else{
                return $this->apmPool->get($commandCode);
            }
        }
        if (!empty($methodCode) && $this->omiseHelper->isAllowUpa($methodCode)) {
            return $this->upaPool->get($commandCode);
        }
        return $this->apmPool->get($commandCode);
    }
}