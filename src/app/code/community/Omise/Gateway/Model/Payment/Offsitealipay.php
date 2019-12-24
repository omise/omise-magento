<?php
class Omise_Gateway_Model_Payment_Offsitealipay extends Omise_Gateway_Model_Payment_Base_Payment
{

    /**
     * @var string
     */
    protected $_code = 'omise_offsite_alipay';

    /**
     * @var string
     */
    protected $_formBlockType = 'omise_gateway/form_offsitealipay';

    /**
     * @var string
     */
    protected $_infoBlockType = 'payment/info';

    /**
     * @var string
     */
    protected $_callbackUrl = 'omise/callback_validateoffsitealipay';

    /**
     * @var array
     */
    protected $_currencies = array('THB');

    /**
     * Payment Method features
     *
     * @var bool
     */
    protected $_isGateway          = true;
    protected $_canReviewPayment   = true;
    protected $_isInitializeNeeded = true;

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
