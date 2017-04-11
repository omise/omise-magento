<?php
namespace Omise\Payment\Model\Validator\Payment;

use Omise\Payment\Model\Validator\Payment\ResultValidator;
use Omise\Payment\Gateway\Validator\Message\Invalid;

class AuthorizeResultValidator extends ResultValidator
{
    /**
     * @param  mixed
     *
     * @return mixed
     */
    public function validate($data)
    {
        $validate = parent::validate($data);
        if ($validate instanceof Invalid) {
            return $validate;
        }

        if ($data['status'] === 'pending' && $data['authorized'] == true) {
            return true;
        }

        return new Invalid('Payment failed, invalid payment status, please contact our support if you have any questions');
    }
}
