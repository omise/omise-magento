<?php
abstract class Omise_Gateway_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    /**
     * @var \Omise_Gateway_Model_Config
     */
    protected $config;

    /**
     * Omise's public key
     *
     * @var string
     */
    protected $public_key;

    /**
     * Omise's secret key
     *
     * @var string
     */
    protected $secret_key;

    /**
     * @var \Mage_Sales_Model_Order_Payment
     */
    protected $payment_information;

    /**
     * Load necessary file and setup Omise keys
     *
     * @return void
     */
    public function __construct()
    {
        // Load Omise-PHP library
        require_once(Mage::getBaseDir('lib') . '/omise-php/lib/Omise.php');

        $this->config = Mage::getModel('omise_gateway/config')->load(1);

        if ($this->isTestMode()) {
            $this->public_key = $this->config->public_key_test;
            $this->secret_key = $this->config->secret_key_test;
        } else {
            $this->public_key = $this->config->public_key;
            $this->secret_key = $this->config->secret_key;
        }
    }

    /**
     * @return bool
     */
    public function isTestMode()
    {
        return $this->config->test_mode;
    }

    /**
     * @return \Mage_Sales_Model_Order_Payment
     */
    public function getPaymentInformation()
    {
        return $this->payment_information;
    }

    /**
     * @param  \Omise_Gateway_Model_Strategies_StrategyInterface $strategy
     * @param  \Mage_Sales_Model_Order_Payment                   $payment
     * @param  int|float                                         $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function perform(
        Omise_Gateway_Model_Strategies_StrategyInterface $strategy,
        Mage_Sales_Model_Order_Payment $payment,
        $amount
    ) {
        $this->defineOmiseKeys();
        $this->defineOmiseApiVersion();
        $this->defineOmiseUserAgent();

        $this->payment_information = $payment;

        try {
            $result = $strategy->perform($this, $amount);
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('payment')->__($e->getMessage()));
        }

        if (! $strategy->validate($result)) {
            Mage::throwException(Mage::helper('payment')->__($strategy->getMessage()));
        }    

        return $result;
    }

    /**
     * @param  string $public_key
     * @param  string $secret_key
     *
     * @return void
     */
    protected function defineOmiseKeys($public_key = '', $secret_key = '')
    {
        if (! defined('OMISE_PUBLIC_KEY')) {
            define('OMISE_PUBLIC_KEY', $public_key ? $public_key : $this->public_key);
        }

        if (! defined('OMISE_SECRET_KEY')) {
            define('OMISE_SECRET_KEY', $secret_key ? $secret_key : $this->secret_key);
        }
    }

    /**
     * @param  string $version
     *
     * @return void
     */
    protected function defineOmiseApiVersion($version = '2014-07-27')
    {
        if (! defined('OMISE_API_VERSION')) {
            define('OMISE_API_VERSION', $version);
        }
    }

    /**
     * @return void
     */
    protected function defineOmiseUserAgent()
    {
        if (! defined('OMISE_USER_AGENT_SUFFIX')) {
            define('OMISE_USER_AGENT_SUFFIX', 'OmiseMagento/1.9.0.6 Magento/' . Mage::getVersion());
        }
    }
}
