<?php
namespace Omise\Payment\Model\Validator\Payment;

use Omise\Payment\Gateway\Validator\Message\Invalid;
use Omise\Payment\Gateway\Validator\Message\OmiseObjectInvalid;

class ResultValidator
{
    /**
     * @param  mixed
     *
     * @return mixed
     */
    public function validate($data)
    {
        if (! isset($data['object']) || $data['object'] !== 'charge') {
            return new OmiseObjectInvalid();
        }

        if ($data['status'] === 'failed') {
            return new Invalid('Payment failed. ' . ucfirst($data['failure_message']) . ', please contact our support if you have any questions.');
        }

        return true;
    }
}
