<?php
class Omise_Gateway_Model_OmiseAccount extends Omise_Gateway_Model_Omise
{
    /**
     * Retrieve user's account from their Omise account
     *
     * @return OmiseAccount|Exception
     */
    public function retrieveOmiseAccount()
    {
        $this->initNecessaryConstant();

        try {
            return OmiseAccount::retrieve($this->getPublicKey(), $this->getSecretKey());
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }
}
