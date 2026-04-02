<?php 
namespace Omise\Payment\Model;

use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Omise\Payment\Helper\OmiseHelper;
use Magento\Checkout\Model\Session;

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
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/omise-upa.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('***AMP COMMAND POOL***');

        $quote = $this->checkoutSession->getQuote();
        $methodCode = null;

        if ($quote && $quote->getPayment()) {
            $methodCode = $quote->getPayment()->getMethod();
        }

        if (!empty($methodCode) && $this->omiseHelper->isAllowUpa($methodCode)) {
            $logger->info('***CONFIGURATION ALLOWD UPA***');
            return $this->upaPool->get($commandCode);
        }

        return $this->apmPool->get($commandCode);
    }
}