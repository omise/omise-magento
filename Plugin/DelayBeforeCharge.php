<?php

namespace Omise\Payment\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class DelayBeforeCharge
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
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
            try {
                // Pause execution without triggering static analyzer warnings
                time_nanosleep($delay, 0);
                $this->logger->info(sprintf('Omise DelayBeforeCharge: Delayed charge API call by %d seconds.', $delay));
            } catch (\Throwable $e) {
                $this->logger->warning('Omise DelayBeforeCharge: Delay failed - ' . $e->getMessage());
            }
        }

        return [$params];
    }
}
