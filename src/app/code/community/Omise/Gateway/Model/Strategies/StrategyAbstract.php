<?php
abstract class Omise_Gateway_Model_Strategies_StrategyAbstract extends Omise_Gateway_Model_Omise implements
    Omise_Gateway_Model_Strategies_StrategyInterface
{
    /**
     * @var string
     */
    protected $message;

    /**
     * @param  string $public_key
     * @param  string $secret_key
     *
     * @return void
     */
    protected function defineOmiseKeys($public_key = '', $secret_key = '')
    {
        if (! defined('OMISE_PUBLIC_KEY')) {
            define('OMISE_PUBLIC_KEY', $public_key ? $public_key : $this->_public_key);
        }

        if (! defined('OMISE_SECRET_KEY')) {
            define('OMISE_SECRET_KEY', $secret_key ? $secret_key : $this->_secret_key);
        }
    }

    /**
     * Execute a payment command.
     *
     * @param  array|mixed $params
     *
     * @return mixed
     */
    public function execute($params = array())
    {
        try {
            $this->defineOmiseKeys();
            return $this->process($params);
        } catch (Exception $e) {
            return array(
                'object'  => 'error',
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Process a payment.
     *
     * @param  array|mixed $params
     *
     * @return mixed
     */
    abstract public function process($params = array());

    /**
     * Validate a payment process result.
     *
     * @param  mixed $data
     *
     * @return boolean
     */
    abstract public function validate($data);

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
