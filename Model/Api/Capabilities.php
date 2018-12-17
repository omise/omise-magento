<?php

namespace Omise\Payment\Model\Api;

use OmiseCapabilities;

class Capabilities extends Object
{
    private $capabilities;

    public function __construct(\PSR\Log\LoggerInterface $log) {
        $this->capabilities = OmiseCapabilities::retrieve();
        $log->debug('from capability api construct', ['obj'=>$this->capabilities]);
    }

    public function read() {
        return $this->capabilities->getBackends(
            $this->capabilities->backendTypeIs('installment')
        );    
    }
}
