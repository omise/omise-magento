<?php
class Omise_Gateway_Model_Omise extends Mage_Core_Model_Abstract
{
    /**
     * @deprecated use $this->getPublicKey(); instead.
     *
     * @var string  Omise public key
     */
    protected $_public_key;

    /**
     * @deprecated use $this->getSecretKey(); instead.
     *
     * @var string  Omise secret key
     */
    protected $_secret_key;

    /**
     * @var \Omise_Gateway_Model_Config
     */
    protected $config;

    /**
     * Load necessary file and setup Omise keys
     *
     * @return void
     */
    protected function _construct()
    {
        // Load Omise-PHP library
        require_once(Mage::getBaseDir('lib') . '/omise-php/lib/Omise.php');

        // Retrieve Omise's keys from table.
        $this->config = Mage::getModel('omise_gateway/config')->load(1);

        // @deprecated use $this->getPublicKey() and $this->getSecretKey() instead.
        $this->_public_key = $this->getPublicKey();
        $this->_secret_key = $this->getSecretKey();
    }

    /**
     * @return bool
     */
    public function isTestMode()
    {
        return $this->config->test_mode;
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        if ($this->isTestMode()) {
            return $this->config->public_key_test;
        }

        return $this->config->public_key;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        if ($this->isTestMode()) {
            return $this->config->secret_key_test;
        }

        return $this->config->secret_key;
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
    public function defineApiVersion($version = '2014-07-27')
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
            define('OMISE_USER_AGENT_SUFFIX', 'OmiseMagento/1.11 Magento/' . Mage::getVersion());
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
