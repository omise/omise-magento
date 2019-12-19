<?php
class Omise_Gateway_Model_Payment_Offsiteinstallment extends Omise_Gateway_Model_Payment_Base_Payment
{
    public function __construct()
    {
        $this->setCode('omise_offsite_installment');
        $this->setFormBlockType('omise_gateway/form_offsiteinstallment');
        $this->setInfoBlockType('omise_gateway/info_installment');
        $this->setIsGateway(true);
        $this->setCanReviewPayment(true);
        $this->setIsInitializeNeeded(true);
        $this->setCallbackUrl('omise/callback_validateoffsiteinstallment');
        parent::__construct();
    }

    /**
     * Get an array of installment backends suitable for this transaction
     *
     * @return array
     */
    public function getValidBackends()
    {
        $quote = Mage::helper('checkout')->getQuote();
        $currencyCode = $quote->getBaseCurrencyCode();
        $amount = $quote->getBaseGrandTotal();

        return Mage::getModel('omise_gateway/api_capabilities')->getBackends(
            'installment',
            $currencyCode,
            $this->getAmountInSubunits($amount, $currencyCode)
        );
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
        $currency = $order->getBaseCurrencyCode();

        return parent::_process(
            $payment,
            array(
                'amount'      => $this->getAmountInSubunits($amount, $currency),
                'currency'    => $currency,
                'description' => 'Processing payment with installments. Magento order ID: ' . $order->getIncrementId(),
                'source'      => array(
                    'type' => $payment->getAdditionalInformation('type'),
                    'installment_terms' => $payment->getAdditionalInformation('terms')
                ),
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

        $type = $data->getData('type');

        $this->getInfoInstance()->setAdditionalInformation('type', $type);
        $this->getInfoInstance()->setAdditionalInformation('terms', $data->getData('terms_'.$type));
    }
}
