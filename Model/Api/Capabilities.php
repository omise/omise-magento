<?php

namespace Omise\Payment\Model\Api;

use Exception;
use OmiseCapabilities;

class Capabilities extends Object
{
    public function __construct()
    {
        try {
            $this->refresh(OmiseCapabilities::retrieve());
        } catch (Exception $e) {
            return null;
        }

        return $this;
    }
}
