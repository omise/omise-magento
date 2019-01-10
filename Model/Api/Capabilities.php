<?php

namespace Omise\Payment\Model\Api;

use OmiseCapabilities;

class Capabilities extends Object
{
    private $capabilities;

    public function __construct() {
        $this->capabilities = OmiseCapabilities::retrieve();
    }

    /**
     * Get Installment capabilities array from Omise-PHP
     * 
     * @return array
     */
    public function retrieve() {
        return $this->capabilities->getBackends(
            $this->capabilities->backendTypeIs('installment')
        );    
    }
}
