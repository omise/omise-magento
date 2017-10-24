<?php
class Omise_Gateway_Model_Payment_Offsiteinternetbanking extends Omise_Gateway_Model_Payment
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
    protected $_isGateway        = true;
    protected $_canCapture       = true;
    protected $_canReviewPayment = true;

    /**
     * Capture payment method
     *
     * @param  Varien_Object $payment
     * @param  float         $amount
     *
     * @return self
     */
    public function capture(Varien_Object $payment, $amount)
    {
        Mage::log('Omise: processing internet banking payment.');

        $order  = $payment->getOrder();
        $charge = $this->process(
            $payment,
            array(
                'amount'      => $this->getAmountInSubunits($amount, $order->getOrderCurrencyCode()),
                'currency'    => $order->getOrderCurrencyCode(),
                'description' => 'Charge a card from Magento that order id is ' . $order->getIncrementId(),
                'offsite'     => $payment->getAdditionalInformation('offsite'),
                'return_uri'  => $this->getCallbackUri()
            )
        );

        if ($charge->isAwaitForPayment()) {
            return $this;
        }

        $this->suspectToBeFailed($payment);
    }

    /**
     * {@inheritDoc}
     *
     * @see app/code/community/Omise/Gateway/Model/Payment/Creditcard.php
     */
    public function acceptPayment(Mage_Payment_Model_Info $payment)
    {
        parent::acceptPayment($payment);

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @see app/code/community/Omise/Gateway/Model/Payment/Creditcard.php
     */
    public function denyPayment(Mage_Payment_Model_Info $payment)
    {
        parent::denyPayment($payment);

        return true;
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

    /**
     * {@inheritDoc}
     *
     * @see app/code/core/Mage/Sales/Model/Quote/Payment.php
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getSingleton('checkout/session')->getOmiseAuthorizeUri();
    }

    /**
     * @param  array $params
     *
     * @return string
     */
    public function getCallbackUri($params = array())
    {
        return Mage::getUrl(
            'omise/callback_validateoffsiteinternetbanking',
            array(
                '_secure' => Mage::app()->getStore()->isCurrentlySecure(),
                '_query'  => $params
            )
        );
    }
}
