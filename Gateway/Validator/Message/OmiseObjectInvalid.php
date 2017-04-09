<?php
namespace Omise\Payment\Gateway\Validator\Message;

use Omise\Payment\Gateway\Validator\Message\Invalid;

class OmiseObjectInvalid extends Invalid
{
    /**
     * @var string
     */
    protected $message = 'Transaction has been declined. Please contact administrator';
}
