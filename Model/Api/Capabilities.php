<?php

namespace Omise\Payment\Model\Api;

use OmiseCapabilities;

class Capabilities extends Object
{
    private $capabilities;

    public function __construct() {
        $this->capabilities = OmiseCapabilities::retrieve();
    }

    public function read() {
        return $this->capabilities->getBackends(
            $this->capabilities->backendTypeIs('installment')
        );    
    }
}
