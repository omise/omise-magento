<?php
class Omise_Gateway_Model_OmiseCharge extends Omise_Gateway_Model_Omise
{

    /**
     * @param string $id
     * @return OmiseTransfer|array
     */
    public function retrieveOmiseCharge($params)
    {   
        if(!isset($params['limit']))
            $params['limit'] = 10;

        try {

            $check = OmiseCharge::retrieve('', $this->_public_key, $this->_secret_key);
            $params = array(
                'offset' => $check['total'] - $params['limit'],
                'limit' => $params['limit']
            );
            return OmiseCharge::retrieve("?" . http_build_query($params), $this->_public_key, $this->_secret_key);
             
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

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

    /**
     * Capture a charge that retrieve from charge id
     * @param array $params
     * @return OmiseCharge|Exception
     */
    public function captureOmiseCharge($id)
    {
        try {
            $charge = OmiseCharge::retrieve($id, $this->_public_key, $this->_secret_key);
            return $charge->capture();
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }
}