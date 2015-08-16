<?php
class Omise_Gateway_Model_OmiseTransfer extends Omise_Gateway_Model_Omise
{
    /**
     * @param string $id
     * @return OmiseTransfer|array
     */
    public function retrieveOmiseTransfer($params)
    {
        if(!isset($params['page']))
            $params['page'] = 1;

        if(!isset($params['limit']))
            $params['limit'] = 10;

        try {

            $check = OmiseTransfer::retrieve('', $this->_public_key, $this->_secret_key);
            $start = ($params['limit'] * $params['page']);
            $offset = ($start > $check['total']) ? 0 : ($check['total'] - $start);
            $limit = ($start > $check['total']) ? ($check['total'] - $params['limit']) : $params['limit'];
            $params = array(
                'offset' => $offset,
                'limit' => $limit
            );
            return OmiseTransfer::retrieve("?" . http_build_query($params), $this->_public_key, $this->_secret_key);
             
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
        
    }

    /**
     * @param unknown $params
     * @return OmiseTransfer|array
     */
    public function createOmiseTransfer($params)
    {
        try {
            // Validate $params
            // If it not contain `amount` key.
            if (!isset($params['amount']))
                throw new Exception("Amount was required", 1);

            if ($params['amount'] == '')
                throw new Exception("Don't let amount with empty value", 1);

            // Remove `.`
            $params['amount'] = str_replace('.', '', $params['amount']);

            return OmiseTransfer::create($params, $this->_public_key, $this->_secret_key);
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    /**
     * @param string $id
     * @return OmiseTransfer|array
     */
    public function deleteOmiseTransfer($id = '')
    {
        try {
            if ($id == '')
                throw new Exception("Id was required", 1);

            $object = OmiseTransfer::retrieve($id, $this->_public_key, $this->_secret_key);

            return $object->destroy();
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }
}