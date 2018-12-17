<?php

namespace Omise\Payment\Model\Api;

class Error extends BaseObject
{
    /**
     * @var string
     */
    protected $code    = 'unexpected_error';
    protected $message = 'There is an unexpected error happened, please contact our support for further investigation.';

    public function __construct($error = [])
    {
        isset($error['code']) ? $this->setCode($error['code']) : '';
        isset($error['message']) ? $this->setMessage($error['message']) : '';
    }

    /**
     * @param string $code
     */
    protected function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @param string $message
     */
    protected function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
