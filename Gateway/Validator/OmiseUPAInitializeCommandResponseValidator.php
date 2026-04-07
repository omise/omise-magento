<?php
namespace Omise\Payment\Gateway\Validator;

use Omise\Payment\Gateway\Validator\CommandResponseValidator;
use Omise\Payment\Gateway\Validator\Message\Invalid as ErrorInvalid;
use Omise\Payment\Model\Api\Charge;

class OmiseUPAInitializeCommandResponseValidator extends CommandResponseValidator
{
    public function validate(array $validationSubject)
    {   
        $checkoutSession = $validationSubject['response']['session'];
        if (! $checkoutSession instanceof \Omise\Payment\Model\Api\CheckoutSession) {
            return $this->createResult(false, [ (new ErrorResponseInvalid)->getMessage()]);
        }
        $result = $this->validateResponse($checkoutSession);
        if ($result === true) {
            return $this->createResult(true, []);
        }
        return $this->createResult(false, [ $result->getMessage() ]);
    }

    /**
     * @param  array
     *
     * @return true|\Omise\Payment\Gateway\Validator\Message\*
     */
    protected function validateResponse($checkoutSession)
    {
        if(empty($checkoutSession->id) && $checkoutSession->object != "checkout_session"){
            return new ErrorInvalid(
                'Payment failed, invalid payment status,
                please contact our support if you have any questions'
            );
        }
        return true;
    }
}
