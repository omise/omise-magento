<?php
class Omise_Gateway_Model_Payment_Offsiteinternetbanking extends Omise_Gateway_Model_Payment_Base_Payment
{
    public function __construct()
    {
        $this->setCode('omise_offsite_internet_banking');
        $this->setFormBlockType('omise_gateway/form_offsiteinternetbankingpayment');
        $this->setInfoBlockType('omise_gateway/info_internetbanking');
        $this->setIsGateway(true);
        $this->setCanReviewPayment(true);
        $this->setIsInitializeNeeded(true);
        $this->setCallbackUrl('omise/callback_validateoffsiteinternetbanking');
        parent::__construct();
    }

    /**
     * @param  Varien_Object $payment
     * @param  float         $amount
     *
     * @return Omise_Gateway_Model_Api_Charge
     */
    public function process(Varien_Object $payment, $amount)
    {
        $order = $payment->getOrder();

        return parent::_process(
            $payment,
            array(
                'amount'      => $this->getAmountInSubunits($amount, $order->getBaseCurrencyCode()),
                'currency'    => $order->getBaseCurrencyCode(),
                'description' => 'Processing payment with Internet Banking. Magento order ID: ' . $order->getIncrementId(),
                'source'      => array('type' => $payment->getAdditionalInformation('type')),
                'return_uri'  => $this->getCallbackUri(),
                'metadata'    => array(
                    'order_id' => $order->getIncrementId()
                )
            )
        );
    }

    /**
     * {@inheritDoc}
     *
     * @see app/code/core/Mage/Payment/Model/Method/Abstract.php
     */
    public function assignData($data)
    {
        parent::assignData($data);
        $this->getInfoInstance()->setAdditionalInformation('type', $data->getData('bank_type'));
    }
}
