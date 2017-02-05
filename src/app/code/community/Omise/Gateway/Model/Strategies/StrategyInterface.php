<?php
interface Omise_Gateway_Model_Strategies_StrategyInterface
{
    /**
     * Perform a payment action.
     * i.e. authorize a payment, capture a charge, etc.
     *
     * @param  \Omise_Gateway_Model_Payment $payment
     * @param  array                        $params
     *
     * @return mixed
     */
    public function perform($payment, $params = array());

    /**
     * Validate a payment process result.
     *
     * @param  mixed $data
     *
     * @return boolean
     */
    public function validate($data);
}
