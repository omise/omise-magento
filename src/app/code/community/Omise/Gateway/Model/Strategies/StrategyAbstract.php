<?php
abstract class Omise_Gateway_Model_Strategies_StrategyAbstract implements
    Omise_Gateway_Model_Strategies_StrategyInterface
{
    /**
     * @var string
     */
    protected $message;

    /**
     * {@inheritDoc}
     */
    abstract public function perform($payment, $params = array());

    /**
     * {@inheritDoc}
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
