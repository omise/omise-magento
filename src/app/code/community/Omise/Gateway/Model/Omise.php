<?php
class Omise_Gateway_Model_Omise extends Mage_Core_Model_Abstract
{
    /**
     * @var \Omise_Gateway_Model_Config
     */
    protected $_config;

    /**
     * Load necessary file and setup Omise keys
     *
     * Note:
     * Here is an internal constructor (different from real __construct() one).
     * (It's recommened by Magento to override this method instead of __construct()).
     *
     * @see Magento: lib/Varien/Object.php
     */
    protected function _construct()
    {
        // Load Omise-PHP library
        require_once(Mage::getBaseDir('lib') . '/omise-php/lib/Omise.php');

        // Retrieve Omise's keys from table.
        $this->_config = Mage::getModel('omise_gateway/config')->load(1);
    }

    /**
     * @return bool
     */
    public function isTestMode()
    {
        return $this->_config->test_mode;
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        if ($this->isTestMode()) {
            return $this->_config->public_key_test;
        }

        return $this->_config->public_key;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        if ($this->isTestMode()) {
            return $this->_config->secret_key_test;
        }

        return $this->_config->secret_key;
    }

    /**
     * @param  string $public_key
     * @param  string $secret_key
     *
     * @return void
     */
    public function defineApiKeys($public_key = '', $secret_key = '')
    {
        if (! defined('OMISE_PUBLIC_KEY')) {
            define('OMISE_PUBLIC_KEY', $public_key ? $public_key : $this->getPublicKey());
        }

        if (! defined('OMISE_SECRET_KEY')) {
            define('OMISE_SECRET_KEY', $secret_key ? $secret_key : $this->getSecretKey());
        }
    }

    /**
     * @param  string $version
     *
     * @return void
     */
    public function defineApiVersion($version = '2017-11-02')
    {
        if (! defined('OMISE_API_VERSION')) {
            define('OMISE_API_VERSION', $version);
        }
    }

    /**
     * @return void
     */
    public function defineUserAgent()
    {
        if (! defined('OMISE_USER_AGENT_SUFFIX')) {
            define('OMISE_USER_AGENT_SUFFIX', 'OmiseMagento/1.21 Magento/' . Mage::getVersion());
        }
    }

    /**
     * @return void
     */
    public function initNecessaryConstant()
    {
        $this->defineApiKeys();
        $this->defineApiVersion();
        $this->defineUserAgent();
    }
}
