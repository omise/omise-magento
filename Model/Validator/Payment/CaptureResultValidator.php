<?php
namespace Omise\Payment\Model\Validator\Payment;

use Omise\Payment\Model\Validator\Payment\ResultValidator;
use Omise\Payment\Gateway\Validator\Message\Invalid;

class CaptureResultValidator extends ResultValidator
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

        $captured = $data['captured'] ? $data['captured'] : $data['paid'];

        if ($data['status'] === 'successful'
            && $data['authorized'] == true
            && $captured == true
        ) {
            return true;
        }

        return new Invalid('Payment failed, invalid payment status, please contact our support if you have any questions');
    }
}
