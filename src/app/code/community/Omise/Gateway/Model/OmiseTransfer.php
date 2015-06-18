<?php
class Omise_Gateway_Model_OmiseTransfer extends Omise_Gateway_Model_Omise
{
    /**
     * @param string $id
     * @return OmiseTransfer|array
     */
    public function retrieveOmiseTransfer($id = '')
    {
        try {
            return OmiseTransfer::retrieve('', $this->_public_key, $this->_secret_key);
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