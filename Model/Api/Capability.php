<?php

namespace Omise\Payment\Model\Api;

use Magento\Framework\Exception\LocalizedException;
use OmiseCapability;
use Omise\Payment\Model\Config\Config;

class Capability extends BaseObject
{
    private $capability;

    private $config;

    /**
     * Injecting dependencies
     * @param \Omise\Payment\Model\Config\Config $config
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
            $this->capability  = OmiseCapability::retrieve();
        } catch (\Exception $e) {
            throw new LocalizedException(__('unable to load OmiseCapability api'));
        }
    }

    /**
     * Get Installment Capability array from Omise-PHP
     *
     * @return array
     */
    public function getInstallmentBackends()
    {
        return $this->capability  ? $this->capability->getPaymentMethods(
            $this->capability->filterPaymentMethodName('installment')
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
        return $this->capability  ? $this->capability ['zero_interest_installments'] : false;
    }

    /**
     * @param string $type
     * @return array|null
     */
    public function getBackendsByType(string $type)
    {
        return $this->capability  ? $this->capability->getPaymentMethods(
            $this->capability->filterPaymentMethodName($type)
        ) : null;
    }

    /**
     * Retrieves details of payment backends from Capability
     *
     * @return array
     */
    public function getPaymentMethods()
    {
        return $this->capability  ? $this->capability->getPaymentMethods() : null;
    }

    /**
     * Get information about tokenization methods
     *
     * @return array
     */
    public function getTokenizationMethods()
    {
        return $this->capability  ? $this->capability ['tokenization_methods'] : null;
    }

    /**
     * Get installment limit amount
     *
     * @return integer
     */
    public function getInstallmentMinLimit()
    {
        return $this->capability  ? $this->capability ['limits']['installment_amount']['min'] : 0;
    }
}
