<?php

class Omise_Gateway_Model_Omise extends Mage_Core_Model_Abstract
{
    /**
     * @var string  Omise public key
     */
    private $_public_key;

    /**
     * @var string  Omise secret key
     */
    private $_secret_key;

    /**
     * Load necessary file and setup Omise keys
     * @return void
     */
    protected function _construct()
    {
        // Load Omise-PHP library
        require_once(Mage::getBaseDir('lib') . '/omise-php/lib/Omise.php');

        // Retrieve Omise's keys from table.
        $omise = Mage::getModel('omise_gateway/config')->load(1);
        $this->_public_key = $omise->public_key;
        $this->_secret_key = $omise->secret_key;

        // Replace keys with test keys if test mode was enabled.
        if ($omise->test_mode) {
            $this->_public_key = $omise->public_key_test;
            $this->_secret_key = $omise->secret_key_test;
        }
    }

    /**
     * Retrieve user's account from their Omise account
     * @return OmiseAccount|Exception
     */
    public function retrieveOmiseAccount()
    {
        try {
            $omise = OmiseAccount::retrieve($this->_public_key, $this->_secret_key);

            return $omise;
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    /**
     * Retrieve user's balance from their Omise account
     * @return OmiseBalance|Exception
     */
    public function retrieveOmiseBalance()
    {
        try {
            $omise = OmiseBalance::retrieve($this->_public_key, $this->_secret_key);

            return $omise;
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    /**
     * @param string $id
     * @return OmiseTransfer|array
     */
    public function retrieveOmiseTransfer($id = '')
    {
        try {
            $omise = OmiseTransfer::retrieve('', $this->_public_key, $this->_secret_key);

            return $omise;
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }
}