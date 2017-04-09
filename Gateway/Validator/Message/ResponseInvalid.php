<?php
namespace Omise\Payment\Gateway\Validator\Message;

use Omise\Payment\Gateway\Validator\Message\Invalid;

class ResponseInvalid extends Invalid
{
    /**
     * @var string
     */
    protected $message = 'Couldn\'t retrieve charge transaction. Please contact administrator.';
}
