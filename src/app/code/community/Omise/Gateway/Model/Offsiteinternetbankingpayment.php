<?php
class Omise_Gateway_Model_OffsiteInternetBankingPayment extends Omise_Gateway_Model_Payment
{
    /**
     * @var string
     */
    const STRATEGY_OFFSITE_INTERNET_BANKING = 'OffsiteInternetBankingStrategy';

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
     * @return Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        Mage::log('Start processing internet banking payment method with Omise payment gateway.');

        $result = $this->perform(
            Mage::getModel('omise_gateway/Strategies_' . self::STRATEGY_OFFSITE_INTERNET_BANKING),
            $payment,
            $amount
        );

        $payment->setIsTransactionPending(true);

        $this->getInfoInstance()->setAdditionalInformation('omise_charge_id', $result['id']);

        Mage::getSingleton('checkout/session')->setOmiseAuthorizeUri($result['authorize_uri']);

        Mage::log('The transaction was created, processing internet banking payment by Omise payment gateway.');
        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @see app/code/community/Omise/Gateway/Model/PaymentMethod.php
     */
    public function acceptPayment(Mage_Payment_Model_Info $payment)
    {
        parent::acceptPayment($payment);

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @see app/code/community/Omise/Gateway/Model/PaymentMethod.php
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
     * Format a Magento's amount to be a small-unit that Omise's API requires.
     * Note, no specific format for JPY currency.
     *
     * @param  string          $currency
     * @param  integer | float $amount
     *
     * @return integer
     */
    public function formatAmount($currency, $amount)
    {
        switch (strtoupper($currency)) {
            case 'THB':
            case 'IDR':
            case 'SGD':
                // Convert to a small unit
                $amount = $amount * 100;
                break;
        }

        return $amount;
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
