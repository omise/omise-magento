<?php

namespace Omise\Payment\Model\Api;

use OmiseCapabilities;

class Capabilities extends BaseObject
{
    private $capabilities;

    public function __construct()
    {
        $this->capabilities = OmiseCapabilities::retrieve();
    }

    /**
     * Get Installment capabilities array from Omise-PHP
     *
     * @return array
     */
    public function getInstallmentBackends()
    {
        return $this->capabilities->getBackends(
            $this->capabilities->makeBackendFilterType('installment')
        );    
    }

    /**
     * Get information about zero interest installments
     *
     * @return bool
     */
    public function isZeroInterest() {
        return $this->capabilities['zero_interest_installments'];
    }
}
