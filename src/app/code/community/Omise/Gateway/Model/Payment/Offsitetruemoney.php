<?php
class Omise_Gateway_Model_Payment_Offsitetruemoney extends Omise_Gateway_Model_Payment_SimpleOffsite_Payment
{

    /**
     * @var string
     */
    protected $_code = 'omise_offsite_truemoney';

    /**
     * @var string
     */
    protected $_formBlockType = 'omise_gateway/form_offsitetruemoney';

    /**
     * @var string
     */
    protected $_infoBlockType = 'omise_gateway/info_truemoney';

    /**
     * @var string
     */
    protected $_callbackUrl = 'omise/callback_validateoffsitetruemoney';

    /**
     * @var array
     */
    protected $_currencies = array('THB');

    /**
     * @var string
     */
    protected $_type = 'truemoney';

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
                'source'      => array('type' => $this->_type, 'phone_number' => $payment->getAdditionalInformation('phone_number')),
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
    }
}