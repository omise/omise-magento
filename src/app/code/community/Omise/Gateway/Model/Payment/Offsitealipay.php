<?php
class Omise_Gateway_Model_Payment_Offsitealipay extends Omise_Gateway_Model_Payment_Base_Payment
{
    public function __construct()
    {
        $this->setCode('omise_offsite_alipay');
        $this->setFormBlockType('omise_gateway/form_offsitealipay');
        $this->setInfoBlockType('payment/info');
        $this->setIsGateway(true);
        $this->setCanReviewPayment(true);
        $this->setIsInitializeNeeded(true);
        $this->setCallbackUrl('omise/callback_validateoffsitealipay');
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
                'description' => 'Processing payment with Alipay. Magento order ID: ' . $order->getIncrementId(),
                'source'      => array('type' => 'alipay'),
                'return_uri'  => $this->getCallbackUri(),
                'metadata'    => array(
                    'order_id' => $order->getIncrementId()
                )
            )
        );
    }
}
