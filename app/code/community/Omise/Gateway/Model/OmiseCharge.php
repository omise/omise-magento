<?php

class Omise_Gateway_Model_OmiseCharge extends Omise_Gateway_Model_Omise
{
    /**
     * Creates a new charge with Omise Payment Gateway.
     * @param array $params
     * @return OmiseCharge|Exception
     */
    public function createOmiseCharge($params)
    {
        try {
            return OmiseCharge::create($params, $this->_public_key, $this->_secret_key);
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }
}