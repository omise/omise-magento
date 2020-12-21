<?php

namespace Omise\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Omise\Payment\Gateway\Validator\Message\ResponseInvalid as ErrorResponseInvalid;
use OmiseRefund;

class RefundResponseValidator extends AbstractValidator
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
        $refund = $validationSubject['response']['refund'];

        if (! $refund instanceof OmiseRefund) {
            return $this->createResult(
                false,
                [ (new ErrorResponseInvalid('Unable to process refund'))->getMessage() ]
            );
        }

        if ($refund instanceof \Omise\Payment\Model\Api\Error) {
            return $this->createResult(false, [ $refund->getMessage() ]);
        }

        $result = $this->validateResponse($refund);
        if ($result === true) {
            return $this->createResult(true, []);
        }

        return $this->createResult(false, [ $result->getMessage() ]);
    }

    /**
     * @param OmiseRefundList $refund
     *
     * @return true|\Omise\Payment\Gateway\Validator\Message\*
     */
    protected function validateResponse($refund)
    {
        return true;
    }
}
