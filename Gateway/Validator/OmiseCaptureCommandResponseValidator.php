<?php
namespace Omise\Payment\Gateway\Validator;

use Omise\Payment\Gateway\Validator\CommandResponseValidator;
use Omise\Payment\Gateway\Validator\Message\Invalid;
use Omise\Payment\Gateway\Validator\Message\OmiseObjectInvalid;

class OmiseCaptureCommandResponseValidator extends CommandResponseValidator
{
    /**
     * @param  mixed
     *
     * @return mixed
     */
    public function validateResponse($data)
    {
        if (! isset($data['object']) || $data['object'] !== 'charge') {
            return new OmiseObjectInvalid();
        }

        if ($data['status'] === 'failed') {
            return new Invalid('Payment failed. ' . ucfirst($data['failure_message']) . ', please contact our support if you have any questions.');
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
