<?php
namespace Omise\Payment\Gateway\Validator;

use Omise\Payment\Gateway\Validator\CommandResponseValidator;
use Omise\Payment\Gateway\Validator\Message\Invalid;
use Omise\Payment\Gateway\Validator\Message\OmiseObjectInvalid;
use Omise\Payment\Model\Validator\Payment\AuthorizeResultValidator;
use Omise\Payment\Model\Validator\Payment\CaptureResultValidator;

class ThreeDSecureCommandResponseValidator extends CommandResponseValidator
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

        // Try validate for none 3-D Secure account case before mark as invalid
        if ($data['capture']) {
            $result = (new CaptureResultValidator)->validate($data);
        } else {
            $result = (new AuthorizeResultValidator)->validate($data);
        }

        if ($result === true) {
            return true;
        }

        return new Invalid('Payment failed, invalid payment status, please contact our support if you have any questions');
    }
}
