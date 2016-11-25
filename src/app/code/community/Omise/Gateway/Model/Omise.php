<?php
// Define 'OMISE_USER_AGENT_SUFFIX'
if(!defined('OMISE_USER_AGENT_SUFFIX'))
    define('OMISE_USER_AGENT_SUFFIX', 'OmiseMagento/1.9.0.6 Magento/'.Mage::getVersion());

// Define 'OMISE_API_VERSION'
if(!defined('OMISE_API_VERSION'))
    define('OMISE_API_VERSION', '2014-07-27');

class Omise_Gateway_Model_Omise extends Mage_Core_Model_Abstract
{
    /**
     * @var string  Omise public key
     */
    protected $_public_key;

    /**
     * @var string  Omise secret key
     */
    protected $_secret_key;

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
}