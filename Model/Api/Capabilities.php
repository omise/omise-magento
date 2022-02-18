<?php

namespace Omise\Payment\Model\Api;

use Magento\Framework\Exception\LocalizedException;
use OmiseCapabilities;

class Capabilities extends BaseObject
{
    private $capabilities;

    public function __construct()
    {
        try {
            $this->capabilities = OmiseCapabilities::retrieve();
        } catch (\Exception $e) {
            throw new LocalizedException(__('unable to load OmiseCapabilities api') . ' - Error: '. $e->getMessage());
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
