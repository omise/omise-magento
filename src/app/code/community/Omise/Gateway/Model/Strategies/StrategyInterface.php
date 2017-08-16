<?php
interface Omise_Gateway_Model_Strategies_StrategyInterface
{
    /**
     * Perform a payment action.
     * i.e. authorize a payment, capture a charge, etc.
     *
     * @param  object    $payment
     * @param  int|float $amount
     *
     * @return mixed
     */
    public function perform($payment, $amount);

    /**
     * Validate a payment process result.
     *
     * @param  mixed $data
     *
     * @return boolean
     */
    public function validate($data);
}
