<?php

namespace Omise\Payment\Model\Api;

use Magento\Framework\Exception\LocalizedException;
use OmiseCapabilities;
use Omise\Payment\Model\Config\Config;
use Magento\Store\Model\StoreManagerInterface;

class Capabilities extends BaseObject
{
    private $capabilities;

    /**
     * Injecting dependencies
     * @param \Omise\Payment\Model\Config\Config $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(Config $config, StoreManagerInterface $storeManager)
    {
        $this->config = $config;
        $this->storeManager = $storeManager;

        $this->initOmise();
    }

    /**
     * Initialize the Omise plugin
     */
    private function initOmise()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $this->config->setStoreId($storeId);

        // Initialize only if both keys are present
        if (!$this->config->getPublicKey() || !$this->config->getSecretKey()) {
            return false;
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
}
