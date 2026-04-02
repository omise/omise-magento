<?php
namespace Omise\Payment\Gateway\Validator;

use Omise\Payment\Gateway\Validator\CommandResponseValidator;
use Omise\Payment\Gateway\Validator\Message\Invalid as ErrorInvalid;
use Omise\Payment\Model\Api\Charge;

class OmiseUPAInitializeCommandResponseValidator extends CommandResponseValidator
{
    public function validate(array $validationSubject)
    {
        if(array_key_exists('session',$validationSubject['response'])){
            $checkoutSession = $validationSubject['response']['session'];
            if(array_key_exists('object',$checkoutSession) && $checkoutSession['object'] == 'checkout_session' && !empty($checkoutSession['id'])){
                return $this->createResult(true, []);        
            }
        }
        /*if (! $charge instanceof \Omise\Payment\Model\Api\BaseObject) {
            return $this->createResult(false, [ (new ErrorResponseInvalid)->getMessage() ]);
        }

        if ($charge instanceof \Omise\Payment\Model\Api\Error) {
            return $this->createResult(false, [ $charge->getMessage() ]);
        }*/

        $result = $this->validateResponse($checkoutsession);
        if ($result === true) {
            return $this->createResult(true, []);
        }

        return $this->createResult(false, [ '' ]);
    }

    /**
     * @param  array
     *
     * @return true|\Omise\Payment\Gateway\Validator\Message\*
     */
    protected function validateResponse($checkoutsession)
    {
        if(!array_key_exists("session",$checkoutsession) || !array_key_exists('object',$checkoutsession['session']) || $checkoutsession['session']['object'] != "checkout_session"){
            new ErrorInvalid(
                'Payment failed, invalid payment status,
                please contact our support if you have any questions'
            );
        }else{
            return true;
        }
    }
}
