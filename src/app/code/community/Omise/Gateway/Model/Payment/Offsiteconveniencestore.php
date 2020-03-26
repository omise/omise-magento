<?php
class Omise_Gateway_Model_Payment_Offsiteconveniencestore extends Omise_Gateway_Model_Payment_SimpleOffsite_Payment
{

    /**
     * @var string
     */
    protected $_code = 'omise_offsite_conveniencestore';

    /**
     * @var string
     */
    protected $_formBlockType = 'omise_gateway/form_offsiteconveniencestore';

    /**
     * @var string
     */
    protected $_infoBlockType = 'omise_gateway/info_conveniencestore';

    /**
     * @var string
     */
    protected $_callbackUrl = 'omise/callback_validateoffsiteconveniencestore';

    /**
     * @var array
     */
    protected $_currencies = array('JPY');

    /**
     * @var string
     */
    protected $_type = 'econtext';

    /**
     * Payment Method features
     *
     * @var bool
     */
    protected $_isGateway          = true;
    protected $_canReviewPayment   = true;
    protected $_isInitializeNeeded = true;

    /**
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Omise_Gateway_Model_Api_Charge
     * @throws Mage_Core_Exception
     */
    public function process(Varien_Object $payment, $amount) {
        $order = $payment->getOrder();

        return parent::_process(
            $payment,
            array(
                'amount'      => $this->getAmountInSubunits($amount, $order->getBaseCurrencyCode()),
                'currency'    => $order->getBaseCurrencyCode(),
                'description' => 'Processing payment with '.$this->getPaymentTitle().'. Magento order ID: ' . $order->getIncrementId(),
                'source'      => array(
                    'type' => $this->_type,
                    'phone_number' => $payment->getAdditionalInformation('phone_number'), 
                    'email' => $payment->getAdditionalInformation('email'),
                    'name' => $payment->getAdditionalInformation('name')
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
        $this->getInfoInstance()->setAdditionalInformation('phone_number', $data->getData('phone_number'));
        $this->getInfoInstance()->setAdditionalInformation('email', $data->getData('email'));
        $this->getInfoInstance()->setAdditionalInformation('name', $data->getData('name'));
    }
}
