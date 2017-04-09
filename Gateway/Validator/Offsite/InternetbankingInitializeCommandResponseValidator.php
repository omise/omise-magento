<?php
namespace Omise\Payment\Gateway\Validator\Offsite;

use Omise\Payment\Gateway\Validator\CommandResponseValidator;
use Omise\Payment\Gateway\Validator\Message\Invalid;
use Omise\Payment\Gateway\Validator\Message\OmiseObjectInvalid;

class InternetbankingInitializeCommandResponseValidator extends CommandResponseValidator
{
    /**
     * @param  mixed
     *
     * @return mixed
     */
    protected function validateResponse($data)
    {
        if (! isset($data['object']) || $data['object'] !== 'charge') {
            return new OmiseObjectInvalid();
        }

        if ($data['status'] === 'failed') {
            return new Invalid('Payment failed. ' . ucfirst($data['failure_message']) . ', please contact our support if you have any questions.');
        }

        $captured = $data['captured'] ? $data['captured'] : $data['paid'];

        if ($data['status'] === 'pending'
            && $data['authorized'] == false
            && $captured == false
            && $data['authorize_uri']
        ) {
            return true;
        }

        return new Invalid('Payment failed, invalid payment status, please contact our support if you have any questions');
    }
}
