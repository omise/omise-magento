<?php
class Omise_Gateway_Model_PaymentMethod extends Omise_Gateway_Model_Payment
{
    /**
     * Payment strategies
     *
     * @var string
     */
    const STRATEGY_AUTHORIZE         = 'AuthorizeStrategy';
    const STRATEGY_AUTHORIZE_CAPTURE = 'CaptureStrategy';
    const STRATEGY_MANUAL_CAPTURE    = 'ManualCaptureStrategy';

    /**
     * @var string
     */
    protected $_code          = 'omise_gateway';

    /**
     * @var string
     */
    protected $_formBlockType = 'omise_gateway/form_cc';

    /**
     * @var string
     */
    protected $_infoBlockType = 'payment/info_cc';

    /**
     * Payment Method features
     *
     * @var bool
     */
    protected $_isGateway     = true;
    protected $_canAuthorize  = true;
    protected $_canCapture    = true;

    /**
     * Authorize payment method
     *
     * @param  Varien_Object $payment
     * @param  float         $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        Mage::log('Start authorizing with Omise Payment Gateway.');

        $result = $this->perform(
            Mage::getModel('omise_gateway/Strategies_' . self::STRATEGY_AUTHORIZE),
            $payment,
            $amount
        );

        $this->getInfoInstance()->setAdditionalInformation('omise_charge_id', $result['id']);

        Mage::log('This transaction was authorized! (by OmiseCharge API)');
        return $this;
    }

    /**
     * Capture payment method
     *
     * @param  Varien_Object $payment
     * @param  float         $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        Mage::log('Start capturing with Omise Payment Gateway.');

        if ($payment->getAdditionalInformation('omise_charge_id')) {
            $strategy = self::STRATEGY_MANUAL_CAPTURE;
        } else {
            $strategy = self::STRATEGY_AUTHORIZE_CAPTURE;
        }

        $result = $this->perform(
            Mage::getModel('omise_gateway/Strategies_' . $strategy),
            $payment,
            $amount
        );

        Mage::log('This transaction was authorized and captured! (by OmiseCharge API)');

        return $this;
    }

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     *
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        Mage::log('Assign Data with Omise');

        $result = parent::assignData($data);

        if (is_array($data)) {
            if (! isset($data['omise_token'])) {
                Mage::throwException(Mage::helper('payment')->__('Need Omise\'s keys'));
            }

            Mage::log('Data that assign is Array');
            $this->getInfoInstance()->setAdditionalInformation('omise_token', $data['omise_token']);
        } elseif ($data instanceof Varien_Object) {
            if (! $data->getData('omise_token')) {
                Mage::throwException(Mage::helper('payment')->__('Need Omise\'s keys'));
            }

            Mage::log('Data that assign is Object');
            $this->getInfoInstance()->setAdditionalInformation('omise_token', $data->getData('omise_token'));
        }

        return $result;
    }

    /**
     * Format a Magento's amount to be a small-unit that Omise's API requires.
     * Note, no specific format for JPY currency.
     *
     * @param  string          $currency
     * @param  integer | float $amount
     *
     * @return integer
     */
    public function formatAmount($currency, $amount)
    {
        switch (strtoupper($currency)) {
            case 'THB':
            case 'IDR':
            case 'SGD':
                // Convert to a small unit
                $amount = $amount * 100;
                break;
        }

        return $amount;
    }
}
