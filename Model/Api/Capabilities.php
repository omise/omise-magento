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
}
