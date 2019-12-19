<?php


class Omise_Gateway_Model_Payment_Offsitecitipoints extends Omise_Gateway_Model_Payment_Base_Payment {
    public function __construct()
    {
        $this->setCode('omise_offsite_citipoints');
        $this->setFormBlockType('omise_gateway/form_offsitecitipoints');
        $this->setInfoBlockType('payment/info');
        $this->setIsGateway(true);
        $this->setCanReviewPayment(true);
        $this->setIsInitializeNeeded(true);
        $this->setCallbackUrl('omise/callback_validateoffsitecitipoints');
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
                'amount'       => $this->getAmountInSubunits($amount, $order->getBaseCurrencyCode()),
                'currency'     => $order->getBaseCurrencyCode(),
                'description'  => 'Processing payment with Citi Reward Points. Magento order ID: ' . $order->getIncrementId(),
                'source[type]' => 'points_citi',
                'return_uri'   => $this->getCallbackUri(),
                'metadata'     => array(
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

        $this->getInfoInstance()->setAdditionalInformation('offsite', $data->getData('offsite'));
    }
}