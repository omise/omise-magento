<?php
class Omise_Gateway_Model_Api_Error extends Omise_Gateway_Model_Api_Object
{
    /**
     * @var string
     */
    protected $code    = '';
    protected $message = '';

    public function __construct($error = array())
    {
        $this->setCode((isset($error['code']) ? $error['code'] : 'unexpected_error'));
        $this->setMessage((isset($error['message']) ? $error['message'] : 'There is an unexpected error happened, please contact our support for further investigation.'));
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
