<?php
namespace Omise\Payment\Gateway\Validator\Message;

use Omise\Payment\Gateway\Validator\Message\Invalid;

class ResponseInvalid extends Invalid
{
    /**
     * @var string
     */
    protected $message = 'Transaction has been declined, please contact our support if you have any questions';
}
