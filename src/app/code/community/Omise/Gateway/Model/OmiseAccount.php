<?php
class Omise_Gateway_Model_OmiseAccount extends Omise_Gateway_Model_Omise
{
    /**
     * Retrieve user's account from their Omise account
     * @return OmiseAccount|Exception
     */
    public function retrieveOmiseAccount()
    {
        try {
            return OmiseAccount::retrieve($this->_public_key, $this->_secret_key);
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }
}