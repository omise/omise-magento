<?php
class Omise_Gateway_Model_PaymentMethod extends Omise_Gateway_Model_Payment
{
    /**
     * Payment strategies
     *
     * @var string
     */
    const STRATEGY_AUTHORIZE                        = 'AuthorizeStrategy';
    const STRATEGY_AUTHORIZE_THREE_D_SECURE         = 'AuthorizeThreeDSecureStrategy';
    const STRATEGY_AUTHORIZE_CAPTURE                = 'CaptureStrategy';
    const STRATEGY_AUTHORIZE_CAPTURE_THREE_D_SECURE = 'CaptureThreeDSecureStrategy';
    const STRATEGY_MANUAL_CAPTURE                   = 'ManualCaptureStrategy';

    /**
     * @var string
     */
    protected $_code          = 'omise_gateway';

    /**
     * @var string
     */
    protected $_formBlockType = 'omise_gateway/form_cc';

    /**
     * @var string
     */
    protected $_infoBlockType = 'payment/info_cc';

    /**
     * Payment Method features
     *
     * @var bool
     */
    protected $_isGateway        = true;
    protected $_canAuthorize     = true;
    protected $_canCapture       = true;
    protected $_canReviewPayment = true;

    /**
     * Authorize payment method
     *
     * @param  Varien_Object $payment
     * @param  float         $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        Mage::log('Start authorizing with Omise Payment Gateway.');

        if ($this->isThreeDSecureNeeded()) {
            $result = $this->performAuthorizeThreeDSecure($payment, $amount);
        } else {
            $result = $this->performAuthorize($payment, $amount);
        }

        $this->getInfoInstance()->setAdditionalInformation('omise_charge_id', $result['id']);
        Mage::log('Assigned charge id ' . $result['id'] . ' to the transaction');

        return $this;
    }

    /**
     * Perform authorize action
     *
     * @param  Varien_Object $payment
     * @param  float         $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    protected function performAuthorize(Varien_Object $payment, $amount)
    {
        $result = $this->perform(
            Mage::getModel('omise_gateway/Strategies_' . self::STRATEGY_AUTHORIZE),
            $payment,
            $amount
        );

        Mage::log('The transaction was authorized! (by OmiseCharge API)');
        return $result;
    }

    /**
     * Perform authorize with 3-D Secure action
     *
     * @param  Varien_Object $payment
     * @param  float         $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    protected function performAuthorizeThreeDSecure(Varien_Object $payment, $amount)
    {
        $result = $this->perform(
            Mage::getModel('omise_gateway/Strategies_' . self::STRATEGY_AUTHORIZE_THREE_D_SECURE),
            $payment,
            $amount
        );

        $payment->setIsTransactionPending(true);

        Mage::getSingleton('checkout/session')->setOmiseAuthorizeUri($result['authorize_uri']);

        Mage::log('The transaction was created, processing 3-D Secure authentication.');
        return $result;
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
        Mage::log('Start capturing with Omise Payment Gateway.');

        if ($payment->getAdditionalInformation('omise_charge_id')) {
            $result = $this->performManualCapture($payment, $amount);
        } else if ($this->isThreeDSecureNeeded()) {
            $result = $this->performCaptureThreeDSecure($payment, $amount);
        } else {
            $result = $this->performCapture($payment, $amount);
        }

        $this->getInfoInstance()->setAdditionalInformation('omise_charge_id', $result['id']);
        Mage::log('Assigned charge id ' . $result['id'] . ' to the transaction');

        return $this;
    }

    /**
     * Perform auto capture action
     *
     * @param  Varien_Object $payment
     * @param  float         $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    protected function performCapture(Varien_Object $payment, $amount)
    {
        $result = $this->perform(
            Mage::getModel('omise_gateway/Strategies_' . self::STRATEGY_AUTHORIZE_CAPTURE),
            $payment,
            $amount
        );

        Mage::log('The transaction was authorized and captured by Omise payment gateway.');
        return $result;
    }

    /**
     * Perform auto capture with 3-D Secure action
     *
     * @param  Varien_Object $payment
     * @param  float         $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    protected function performCaptureThreeDSecure(Varien_Object $payment, $amount)
    {
        $result = $this->perform(
            Mage::getModel('omise_gateway/Strategies_' . self::STRATEGY_AUTHORIZE_CAPTURE_THREE_D_SECURE),
            $payment,
            $amount
        );

        $payment->setIsTransactionPending(true);

        Mage::getSingleton('checkout/session')->setOmiseAuthorizeUri($result['authorize_uri']);

        Mage::log('The transaction was created, processing 3-D Secure authentication by Omise payment gateway.');
        return $result;
    }

    /**
     * Capture an authorized transaction
     *
     * @param  Varien_Object $payment
     * @param  float         $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    protected function performManualCapture(Varien_Object $payment, $amount)
    {
        $result = $this->perform(
            Mage::getModel('omise_gateway/Strategies_' . self::STRATEGY_MANUAL_CAPTURE),
            $payment,
            $amount
        );

        Mage::log('The transaction was performed manual capture by Omise payment gateway and successful.');
        return $result;
    }

    /**
     * Attempt to accept a payment that us under review
     *
     * @param  Mage_Payment_Model_Info $payment
     *
     * @return bool
     *
     * @throws Mage_Core_Exception
     */
    public function acceptPayment(Mage_Payment_Model_Info $payment)
    {
        parent::acceptPayment($payment);

        return true;
    }

    /**
     * Attempt to deny a payment that us under review
     *
     * @param  Mage_Payment_Model_Info $payment
     *
     * @return bool
     *
     * @throws Mage_Core_Exception
     */
    public function denyPayment(Mage_Payment_Model_Info $payment)
    {
        parent::denyPayment($payment);

        return true;
    }

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     *
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        Mage::log('Assign Data with Omise');

        $result = parent::assignData($data);

        if (is_array($data)) {
            if (! isset($data['omise_token'])) {
                Mage::throwException(Mage::helper('payment')->__('Cannot retrieve your credit card information. Please make sure that you put a proper card information or contact our support team if you have any questions.'));
            }

            Mage::log('Data that assign is Array');
            $this->getInfoInstance()->setAdditionalInformation('omise_token', $data['omise_token']);
        } elseif ($data instanceof Varien_Object) {
            if (! $data->getData('omise_token')) {
                Mage::throwException(Mage::helper('payment')->__('Cannot retrieve your credit card information. Please make sure that you put a proper card information or contact our support team if you have any questions.'));
            }

            Mage::log('Data that assign is Object');
            $this->getInfoInstance()->setAdditionalInformation('omise_token', $data->getData('omise_token'));
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @see app/code/core/Mage/Sales/Model/Quote/Payment.php
     */
    public function getOrderPlaceRedirectUrl()
    {
        if ($this->isThreeDSecureNeeded()) {
            return Mage::getSingleton('checkout/session')->getOmiseAuthorizeUri();
        }

        return '';
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
     * @return bool
     */
    public function isThreeDSecureNeeded()
    {
        return Mage::getStoreConfig('payment/omise_gateway/threedsecure') ? true : false;
    }

    /**
     * @param  array $params
     *
     * @return string
     */
    public function getThreeDSecureCallbackUri($params = array())
    {
        return Mage::getUrl(
            'omise/callback_validatethreedsecure',
            array(
                '_secure' => Mage::app()->getStore()->isCurrentlySecure(),
                '_query'  => $params
            )
        );
    }

    /**
     * @return bool
     */
    public function isOscSupportEnabled()
    {
        return Mage::getStoreConfig('payment/omise_gateway/osc_support') ? true : false;
    }
}
