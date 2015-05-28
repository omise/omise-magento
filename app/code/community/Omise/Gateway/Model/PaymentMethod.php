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
     * Authorize payment abstract method
     * @param Varien_Object $payment
     * @param float $amount
     * @return Mage_Payment_Model_Abstract
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $omise = Mage::getModel('omise_gateway/omisecharge')->createOmiseCharge(array(
            "amount"        => number_format($amount, 2, '', ''),
            "currency"      => "thb",
            "description"   => 'xxx',
            "card"          => $payment->getData('additional_information')['omise_token']));

        if (isset($omise['error']))
            Mage::throwException(Mage::helper('payment')->__('OmiseCharge:: '.$omise['error']));

        // $payment->setIsTransactionClosed(false);
        Mage::log('Authorize with OmiseCharge API!');
        return $this;
    }

    /**
     * Assign data to info model instance
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        $result = parent::assignData($data);

        if (is_array($data)) {
            if (!isset($data['omise_token']))
                Mage::throwException(Mage::helper('payment')->__('Need Omise\'s keys'));

            $this->getInfoInstance()->setAdditionalInformation('omise_token', $data['omise_token']);
        } elseif ($data instanceof Varien_Object) {
            if (!$data->getData('omise_token'))
                Mage::throwException(Mage::helper('payment')->__('Need Omise\'s keys'));

            $this->getInfoInstance()->setAdditionalInformation('omise_token', $data->getData('omise_token'));
        }

        return $result;
    }
}