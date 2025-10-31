<?php

namespace Omise\Payment\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class DelayBeforeCharge
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Add configurable delay before Omise charge API call
     *
     * @param \Omise\Payment\Model\Api\Charge $subject
     * @param array $params
     * @return array
     */
    public function beforeCreate(\Omise\Payment\Model\Api\Charge $subject, array $params)
    {
        // $delay = (int) $this->scopeConfig->getValue(
        //     'payment/omise/delay_seconds',
        //     ScopeInterface::SCOPE_STORE
        // );
        $delay = 9;
        if ($delay > 0) {
            sleep($delay);
        }

        return [$params];
    }
}
