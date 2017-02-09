<?php
abstract class Omise_Gateway_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    /**
     * @var \Omise_Gateway_Model_Config
     */
    protected $config;

    /**
     * @var \Omise_Gateway_Model_Omise
     */
    protected $omise;

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
        $this->config = Mage::getModel('omise_gateway/config')->load(1);
        $this->omise  = Mage::getModel('omise_gateway/omise');
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
        $this->omise->defineApiKeys();
        $this->omise->defineApiVersion();
        $this->omise->defineUserAgent();

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
}
