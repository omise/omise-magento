<?php
class Omise_Gateway_Model_OffsiteInternetBankingPayment extends Omise_Gateway_Model_Payment
{
    /**
     * @var string
     */
    protected $_code = 'omise_offsite_internet_banking';

    /**
     * @var string
     */
    protected $_formBlockType = 'omise_gateway/form_offsiteinternetbankingpayment';

    /**
     * @var string
     */
    protected $_infoBlockType = 'payment/info';

    /**
     * Payment Method features
     *
     * @var bool
     */
    protected $_isGateway  = true;
    protected $_canCapture = true;

    /**
     * {@inheritDoc}
     *
     * @see app/code/core/Mage/Payment/Model/Method/Abstract.php
     */
    public function assignData($data)
    {
        parent::assignData($data);

        $this->getInfoInstance()->setAdditionalInformation('offsite', $data->getData('offsite'));
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
        Mage::log('Start processing internet banking payment method with Omise payment gateway.');

        Mage::log('The transaction was created, processing internet banking payment by Omise payment gateway.');
        return $this;
    }
}
