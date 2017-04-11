<?php
namespace Omise\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Omise\Payment\Gateway\Validator\Message\Invalid;
use Omise\Payment\Gateway\Validator\Message\ResponseInvalid;

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
        if (! isset($validationSubject['response']) || $validationSubject['response']['object'] !== 'omise') {
            return $this->failed((new ResponseInvalid)->getMessage());
        }

        if ($validationSubject['response']['status'] === 'failed') {
            return $this->failed((new Invalid($validationSubject['response']['message']))->getMessage());
        }

        $result = $this->validateResponse($validationSubject['response']['data']);
        if ($result instanceof Invalid) {
            return $this->failed($result->getMessage());
        }

        return $this->createResult(true, []);
    }

    /**
     * @param  \Magento\Framework\Phrase|string $message
     *
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    protected function failed($message)
    {
        return $this->createResult(false, [ $message ]);
    }

    /**
     * @param  mixed $data
     *
     * @return mixed
     */
    public function validateResponse($data)
    {
        return true;
    }
}
