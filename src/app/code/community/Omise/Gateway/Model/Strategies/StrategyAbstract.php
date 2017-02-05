<?php
abstract class Omise_Gateway_Model_Strategies_StrategyAbstract implements
    Omise_Gateway_Model_Strategies_StrategyInterface
{
    /**
     * @var string
     */
    protected $message;

    /**
     * Perform a payment action.
     * i.e. authorize a payment, capture a charge, etc.
     *
     * @param  \Omise_Gateway_Model_Payment $payment
     * @param  array                        $params
     *
     * @return mixed
     */
    abstract public function perform($payment, $params = array());

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
