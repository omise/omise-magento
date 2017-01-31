<?php
interface Omise_Gateway_Model_Strategies_StrategyInterface
{
    /**
     * Process a payment.
     *
     * @param  array|mixed $params
     *
     * @return mixed
     */
    public function process($params = array());

    /**
     * Validate a payment process result.
     *
     * @param  mixed $data
     *
     * @return boolean
     */
    public function validate($data);

    /**
     * @return string
     */
    public function getMessage();
}
