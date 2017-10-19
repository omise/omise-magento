<?php
class Omise_Gateway_Model_OmiseBalance extends Omise_Gateway_Model_Omise
{
    /**
     * Retrieve user's balance from their Omise account
     *
     * @return OmiseBalance|Exception
     */
    public function retrieveOmiseBalance()
    {
        $this->initNecessaryConstant();

        try {
            return OmiseBalance::retrieve($this->getPublicKey(), $this->getSecretKey());
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }
}
