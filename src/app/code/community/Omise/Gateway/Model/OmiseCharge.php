<?php
class Omise_Gateway_Model_OmiseCharge extends Omise_Gateway_Model_Omise
{

    /**
     * @param string $id
     * @return OmiseTransfer|array
     */
    public function retrieveOmiseCharge($id)
    {   
    
        try {
            $charge = OmiseCharge::retrieve($id, $this->_public_key, $this->_secret_key);
            return $charge;
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    /**
     * @param array $params
     * @return OmiseTransfer|array
     */
    public function retrieveOmiseCharges($params)
    {   
        if(!isset($params['page']))
            $params['page'] = 1;
        
        if(!isset($params['limit']))
            $params['limit'] = 10;

        try {

            $check = OmiseCharge::retrieve('', $this->_public_key, $this->_secret_key);
            $start = ($params['limit'] * $params['page']);
            $offset = ($start > $check['total']) ? 0 : ($check['total'] - $start);
            $limit = ($start > $check['total']) ? ($check['total'] - $params['limit']) : $params['limit'];
            $params = array(
                'offset' => $offset,
                'limit' => $limit
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