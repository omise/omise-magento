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
        $quote = $this->checkoutSession->getQuote();
        $methodCode = null;

        if ($quote && $quote->getPayment()) {
            $methodCode = $quote->getPayment()->getMethod();
        }

        if (!empty($methodCode) && $this->omiseHelper->isAllowUpa($methodCode)) {
            return $this->upaPool->get($commandCode);
        }
        return $this->apmPool->get($commandCode);
    }
}