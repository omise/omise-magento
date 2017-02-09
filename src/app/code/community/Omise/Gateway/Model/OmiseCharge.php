<?php
class Omise_Gateway_Model_OmiseCharge extends Omise_Gateway_Model_Omise
{
    /**
     * Creates a new charge with Omise Payment Gateway.
     *
     * @param  array $params
     *
     * @return OmiseCharge|Exception
     */
    public function createOmiseCharge($params)
    {
        $this->initNecessaryConstant();

        try {
            return OmiseCharge::create($params, $this->_public_key, $this->_secret_key);
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    /**
     * Capture a charge that retrieve from charge id
     *
     * @param  string $id
     *
     * @return OmiseCharge|Exception
     */
    public function captureOmiseCharge($id)
    {
        $this->initNecessaryConstant();

        try {
            $charge = OmiseCharge::retrieve($id, $this->_public_key, $this->_secret_key);
            return $charge->capture();
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }
}
