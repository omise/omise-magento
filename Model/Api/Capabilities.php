<?php

namespace Omise\Payment\Model\Api;

use Magento\Framework\Exception\LocalizedException;
use OmiseCapabilities;
use Omise\Payment\Model\Config\Config;

class Capabilities extends BaseObject
{
    private $capabilities;

    /**
     * Injecting dependencies
     * @param \Omise\Payment\Model\Config\Config $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        $this->init();
    }

    /**
     * Initialize the Omise plugin
     */
    private function init()
    {
        if (!$this->config->canInitialize()) {
            return;
        }

        try {
            $this->capabilities = OmiseCapabilities::retrieve();
        } catch (\Exception $e) {
            throw new LocalizedException(__('unable to load OmiseCapabilities api'));
        }
    }

    /**
     * Get Installment capabilities array from Omise-PHP
     *
     * @return array
     */
    public function getInstallmentBackends()
    {
        return $this->capabilities ? $this->capabilities->getBackends(
            $this->capabilities->makeBackendFilterType('installment')
        )
        : null;
    }

    /**
     * Get information about zero interest installments
     *
     * @return bool
     */
    public function isZeroInterest()
    {
        return $this->capabilities ? $this->capabilities['zero_interest_installments'] : false;
    }

    /**
     * @param string $type
     * @return array|null
     */
    public function getBackendsByType(string $type)
    {
        return $this->capabilities ? $this->capabilities->getBackends(
            $this->capabilities->makeBackendFilterType($type)
        ) : null;
    }

    /**
     * Get Installment capabilities array from Omise-PHP
     *
     * @return array
     */
    public function getBackends()
    {
        return $this->capabilities ? $this->capabilities->getBackends() : null;
    }
}
