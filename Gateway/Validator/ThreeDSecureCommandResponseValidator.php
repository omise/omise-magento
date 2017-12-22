<?php
namespace Omise\Payment\Gateway\Validator;

use Omise\Payment\Gateway\Validator\CommandResponseValidator;
use Omise\Payment\Gateway\Validator\Message\Invalid as ErrorInvalid;
use Omise\Payment\Model\Api\Charge;

class ThreeDSecureCommandResponseValidator extends CommandResponseValidator
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

        if ($charge->isAwaitPayment()) {
            return true;
        }

        // Try validate for none 3-D Secure account case before mark as invalid
        if ($charge->capture) {
            return $charge->isSuccessful() ? true : (new ErrorInvalid('Payment failed, invalid payment status, please contact our support if you have any questions'));
        }

        return $charge->isAwaitCapture() ? true : (new ErrorInvalid('Payment failed, invalid payment status, please contact our support if you have any questions'));
    }
}
