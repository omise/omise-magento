<?php
class Omise_Gateway_Model_PaymentMethod extends Mage_Payment_Model_Method_Cc
{
    /** @var string */
    protected $_code            = 'omise_gateway';

    /** @var string */
    protected $_formBlockType   = 'omise_gateway/form_cc';

    /** @var string */
    protected $_infoBlockType   = 'payment/info_cc';

    /**
     * Payment Method features
     * @var bool
     */
    protected $_isGateway       = true;
    protected $_canAuthorize    = true;
    protected $_canCapture      = true;

    /**
     * Authorize payment method
     * @param Varien_Object $payment
     * @param float $amount
     * @return Mage_Payment_Model_Abstract
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        Mage::log('Start authorize with OmiseCharge API!');
        
        $omise_token = $payment->getData('additional_information');
        $charge = Mage::getModel('omise_gateway/omiseCharge')->createOmiseCharge(array(
            "amount"        => number_format($amount, 2, '', ''),
            "currency"      => "thb",
            "description"   => 'Charge a card from Magento that order id is '.$payment->getData('entity_id'),
            "capture"       => false,
            "card"          => $omise_token['omise_token']
        ));

        if (isset($charge['error']))
            Mage::throwException(Mage::helper('payment')->__('OmiseCharge:: '.$charge['error']));

        $this->getInfoInstance()->setAdditionalInformation('omise_charge_id', $charge['id']);

        Mage::log('This transaction was authorized! (by OmiseCharge API)');
        return $this;
    }

    /**
     * Capture payment method
     * @param Varien_Object $payment
     * @param float $amount
     * @return Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $additional_information = $payment->getData('additional_information');
        $authorized = isset($additional_information['omise_charge_id']) ? $additional_information['omise_charge_id'] : false;
        if ($authorized) {
            // Capture only.
            Mage::log('Start capture with OmiseCharge API!');

            $charge = Mage::getModel('omise_gateway/omiseCharge')->captureOmiseCharge($authorized);
        } else {
            // Authorize and capture.
            Mage::log('Start capture with OmiseCharge API!');

            $omise_token = $payment->getData('additional_information');
            $charge = Mage::getModel('omise_gateway/omiseCharge')->createOmiseCharge(array(
                "amount"        => number_format($amount, 2, '', ''),
                "currency"      => "thb",
                "description"   => 'Charge a card from Magento that order id is '.$payment->getData('entity_id'),
                "card"          => $omise_token['omise_token']
            ));
        }

        if (isset($charge['error']))
            Mage::throwException(Mage::helper('payment')->__('OmiseCharge:: '.$charge['error']));

        Mage::log('This transaction was authorized and captured! (by OmiseCharge API)');
        return $this;
    }


    /**
     * Assign data to info model instance
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        Mage::log('Assign Data with Omise');

        $result = parent::assignData($data);

        if (is_array($data)) {
            if (!isset($data['omise_token']))
                Mage::throwException(Mage::helper('payment')->__('Need Omise\'s keys'));

            Mage::log('Data that assign is Array');
            $this->getInfoInstance()->setAdditionalInformation('omise_token', $data['omise_token']);
        } elseif ($data instanceof Varien_Object) {
            if (!$data->getData('omise_token'))
                Mage::throwException(Mage::helper('payment')->__('Need Omise\'s keys'));

            Mage::log('Data that assign is Object');
            $this->getInfoInstance()->setAdditionalInformation('omise_token', $data->getData('omise_token'));
        }

        return $result;
    }
}