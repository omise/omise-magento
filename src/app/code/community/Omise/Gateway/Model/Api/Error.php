<?php
class Omise_Gateway_Model_Api_Error extends Omise_Gateway_Model_Api_Object
{
    /**
     * @var string
     */
    protected $_code    = '';
    protected $_message = '';

    public function __construct($error = array())
    {
        $this->_setCode((isset($error['code']) ? $error['code'] : 'unexpected_error'));
        $this->_setMessage((isset($error['message']) ? $error['message'] : 'An unexpected error occurred, please contact our support team for further investigation.'));
    }

    /**
     * @param string $code
     */
    protected function _setCode($code)
    {
        $this->_code = $code;
    }

    /**
     * @param string $message
     */
    protected function _setMessage($message)
    {
        $this->_message = $message;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }
}
