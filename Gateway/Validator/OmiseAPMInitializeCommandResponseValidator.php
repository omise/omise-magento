<?php
namespace Omise\Payment\Gateway\Validator;

use Omise\Payment\Gateway\Validator\CommandResponseValidator;
use Omise\Payment\Gateway\Validator\Message\Invalid as ErrorInvalid;
use Omise\Payment\Model\Api\Charge;

class OmiseAPMInitializeCommandResponseValidator extends CommandResponseValidator
{
    /**
     * @param  \Omise\Payment\Model\Api\Charge $charge
     *
     * @return true|\Omise\Payment\Gateway\Validator\Message\*
     */
    protected function validateResponse(Charge $charge)
    {
        if ($charge->isFailed()) {
            return new ErrorInvalid('Payment failed. ' . ucfirst($charge->failure_message) . ', please contact our support if you have any questions.');
        }

        return $charge->isAwaitPayment() ? true : (new ErrorInvalid('Payment failed, invalid payment status, please contact our support if you have any questions'));
    }
}
