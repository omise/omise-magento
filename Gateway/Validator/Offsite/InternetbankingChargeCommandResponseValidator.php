<?php
namespace Omise\Payment\Gateway\Validator\Offsite;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Validator\AbstractValidator;

class InternetbankingChargeCommandResponseValidator extends AbstractValidator
{
    /**
     * @var string
     */
    protected $message;

    /**
     * Performs domain-related validation for business object
     *
     * @param  array $validationSubject
     *
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        return $this->createResult(true, []);
    }
}
