<?php
namespace Omise\Payment\Gateway\Validator;

use Omise\Payment\Gateway\Validator\CommandResponseValidator;
use Omise\Payment\Gateway\Validator\Message\Invalid as ErrorInvalid;
use Omise\Payment\Model\Api\Charge;

class OmiseUPAInitializeCommandResponseValidator extends CommandResponseValidator
{
    public function validate(array $validationSubject)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/omise-upa.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('***AMP COMMAND POOL*** 123');
        $logger->info(print_r($validationSubject,true));
        return $this->createResult(true, []);
        $checkoutsession = $validationSubject['response']['session'];

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
