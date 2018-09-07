<?php
abstract class Omise_Gateway_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    /**
     * @var \Omise_Gateway_Model_Omise
     */
    protected $_omise;

    /**
     * @var array
     */
    private $currency_subunits = array(
        'JPY' => 1,
        'THB' => 100,
        'SGD' => 100,
        'USD' => 100,
        'EUR' => 100,
        'GBP' => 100
    );

    /**
     * Load necessary file and setup Omise keys
     *
     * @return void
     */
    public function __construct()
    {
        $this->_omise = Mage::getModel('omise_gateway/omise');
        $this->_omise->initNecessaryConstant();
    }

    /**
     * @param  string $currency
     *
     * @return bool
     */
    protected function _isCurrencySupport($currency)
    {
        if (isset($this->currency_subunits[strtoupper($currency)])) {
            return true;
        }

        return false;
    }

    /**
     * @param  int    $amount
     * @param  string $currency
     *
     * @return int
     */
    public function getAmountInSubunits($amount, $currency)
    {
        if ($this->_isCurrencySupport($currency)) {
            return $this->currency_subunits[$currency] * $amount;
        }

        return $amount;
    }

    /**
     * @param  Varien_Object $payment
     * @param  array         $params
     *
     * @return Omise_Gateway_Model_Api_Charge
     *
     * @throws Mage_Core_Exception
     */
    protected function _process(Varien_Object $payment, $params)
    {
        $charge = Mage::getModel('omise_gateway/api_charge')->create($params);

        if (! $charge instanceof Omise_Gateway_Model_Api_Charge) {
            Mage::throwException(
                Mage::helper('payment')->__(
                    ($charge instanceof Omise_Gateway_Model_Api_Error) ? $charge->getMessage() : 'Payment failed. Please note that your payment and order might (or might not) have already been processed. Please contact our support team to confirm your payment before attempting to resubmit.'
                )
            );
        }

        $payment->setTransactionId($charge->id);

        if ($charge->isFailed()) {
            Mage::throwException(Mage::helper('payment')->__($charge->failure_message));
        }

        if ($charge->isAwaitPayment()) {
            $this->_setRedirectFlow($payment, $charge);
        }

        $this->getInfoInstance()->setAdditionalInformation('omise_charge_id', $charge->id);

        return $charge;
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

        $this->_closeTransaction($payment);

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

        $this->_closeTransaction($payment);

        return true;
    }

    /**
     * @param Varien_Object $payment
     */
    protected function _closeTransaction(Varien_Object $payment)
    {
        $transaction = $payment->getTransaction($payment->getLastTransId());
        if ($transaction->getTxnType() === Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE) {
            $transaction->closeCapture();
        } else {
            $transaction->close();
        }
    }

    /**
     * Execute this method when buyer makes a payment with those
     * 'redirect' payments (3-D Secure, InternetBanking, Alipay).
     *
     * @param Varien_Object                  $payment
     * @param Omise_Gateway_Model_Api_Charge $charge
     */
    protected function _setRedirectFlow(Varien_Object $payment, Omise_Gateway_Model_Api_Charge $charge)
    {
        $payment->setIsTransactionPending(true);

        Mage::getSingleton('checkout/session')->setOmiseAuthorizeUri($charge->authorize_uri);
    }

    /**
     * Execute this method whenever a charge result doesn't match with any conditions.
     * So you can throw out an error message to warn buyers that there might be something wrong on a transaction
     * and ask them to contact merchant back.
     *
     * @param  Varien_Object $payment
     *
     * @throws Mage_Core_Exception
     */
    protected function _suspectToBeFailed(Varien_Object $payment)
    {
        $message = 'Payment failed. Please note that your payment and order might (or might not) have already been processed. Please contact our support team using your order reference number (' . $payment->getOrder()->getIncrementId() . ') to confirm your payment.';
        Mage::throwException(Mage::helper('payment')->__($message));
    }
}
