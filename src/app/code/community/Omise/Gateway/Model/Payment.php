<?php
abstract class Omise_Gateway_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    /**
     * @var \Omise_Gateway_Model_Omise
     */
    protected $omise;

    /**
     * @var array
     */
    private $currency_subunits = array(
        'JPY' => 1,
        'THB' => 100,
        'SGD' => 100,
        'IDR' => 100,
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
        $this->omise = Mage::getModel('omise_gateway/omise');
        $this->omise->initNecessaryConstant();
    }

    /**
     * @param  string $currency
     *
     * @return bool
     */
    protected function isCurrencySupport($currency)
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
        if ($this->isCurrencySupport($currency)) {
            return $this->currency_subunits[$currency] * $amount;
        }

        return $amount;
    }

    /**
     * @param  Varien_Object $payment
     * @param  array         $params
     *
     * @return Omise_Gateway_Model_Api_Charge
     */
    protected function process($payment, $params = array())
    {
        $charge = Mage::getModel('omise_gateway/api_charge')->create($params);

        if (! $charge instanceof Omise_Gateway_Model_Api_Charge) {
            Mage::throwException(
                Mage::helper('payment')->__(
                    ($charge instanceof Omise_Gateway_Model_Api_Error) ? $charge->getMessage() : 'Payment failed. Note that your payment and order might (or might not) already has been processed. Please contact our support team to confirm your payment before resubmit.'
                )
            );
        }

        if ($charge->isFailed()) {
            Mage::throwException(Mage::helper('payment')->__($charge->failure_message));
        }

        if ($charge->isAwaitForPayment()) {
            $this->setRedirectFlow($payment, $charge);
        }

        $this->getInfoInstance()->setAdditionalInformation('omise_charge_id', $charge->id);

        return $charge;
    }

    /**
     * Execute this method when buyer makes a payment with those
     * 'redirect' payments (3-D Secure, InternetBanking, Alipay).
     *
     * @param Varien_Object                  $payment
     * @param Omise_Gateway_Model_Api_Charge $charge
     */
    public function setRedirectFlow(Varien_Object $payment, Omise_Gateway_Model_Api_Charge $charge)
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
    protected function suspectToBeFailed(Varien_Object $payment)
    {
        $message = 'Payment failed. Note that your payment and order might (or might not) already has been processed. Please contact our support team using your order reference number (' . $payment->getOrder()->getIncrementId() . ') to confirm your payment.';
        Mage::throwException(Mage::helper('payment')->__($message));
    }
}
