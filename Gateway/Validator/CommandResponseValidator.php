<?php

namespace Omise\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Omise\Payment\Gateway\Validator\Message\Invalid as ErrorInvalid;
use Omise\Payment\Gateway\Validator\Message\ResponseInvalid as ErrorResponseInvalid;
use Omise\Payment\Model\Api\Charge;

class CommandResponseValidator extends AbstractValidator
{
    /**
     * Performs domain-related validation for business object
     *
     * @param  array $validationSubject
     *
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $charge = $validationSubject['response']['charge'];

        if (! $charge instanceof \Omise\Payment\Model\Api\BaseObject) {
            return $this->createResult(false, [ (new ErrorResponseInvalid)->getMessage() ]);
        }

        if ($charge instanceof \Omise\Payment\Model\Api\Error) {
            return $this->createResult(false, [ $charge->getMessage() ]);
        }

        $result = $this->validateResponse($charge);
        if ($result === true) {
            return $this->createResult(true, []);
        }

        return $this->createResult(false, [ $result->getMessage() ]);
    }

    /**
     * @param  \Omise\Payment\Model\Api\Charge $charge
     *
     * @return true|\Omise\Payment\Gateway\Validator\Message\*
     */
    protected function validateResponse(Charge $charge)
    {
        return true;
    }
}
