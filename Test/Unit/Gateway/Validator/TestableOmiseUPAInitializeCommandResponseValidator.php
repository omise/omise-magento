<?php

namespace Omise\Payment\Test\Unit\Gateway\Validator;

use PHPUnit\Framework\TestCase;
use Magento\Payment\Gateway\Validator\Result;
use Omise\Payment\Gateway\Validator\OmiseUPAInitializeCommandResponseValidator;

class TestableOmiseUPAInitializeCommandResponseValidator extends OmiseUPAInitializeCommandResponseValidator
{
    public function __construct()
    {
        // Skip parent constructor
    }

    protected function createResult(
        $isValid,
        array $failsDescription = [],
        array $errorCodes = []
    ) {
        return new Result(
            $isValid,
            $failsDescription,
            $errorCodes
        );
    }
}
