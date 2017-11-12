<?php
class Omise_Gateway_Model_Payment_Creditcard extends Omise_Gateway_Model_Payment
{
    /**
     * @var string
     */
    protected $_code = 'omise_gateway';

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
     * flag if we need to run payment initialize while order place
     *
     * @return bool
     */
    public function isInitializeNeeded()
    {
        if ($this->isThreeDSecureNeeded()) {
            return true;
        }

        return parent::isInitializeNeeded();
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string        $payment_action
     * @param Varien_Object $state_object
     */
    public function initialize($payment_action, $state_object)
    {
        $payment = $this->getInfoInstance();
        $order   = $payment->getOrder();

        switch ($payment_action) {
            case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE:
                $charge = $this->processPayment($payment, $order->getBaseTotalDue());

                $payment->setIsTransactionClosed(false)
                        ->setIsTransactionPending(true)
                        ->addTransaction(
                            Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
                            null,
                            false,
                            Mage::helper('omise_gateway')->__('Authorizing an amount %s via Omise 3-D Secure payment.', $order->getBaseCurrency()->formatTxt($order->getBaseTotalDue()))
                        );
                break;

            case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE:
                $invoice = $order->prepareInvoice()->register();

                $charge = $this->processPayment($payment, $invoice->getBaseGrandTotal());

                $payment->setCreatedInvoice($invoice)
                        ->setIsTransactionClosed(false)
                        ->setIsTransactionPending(true)
                        ->addTransaction(
                            Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
                            $invoice,
                            false,
                            Mage::helper('omise_gateway')->__('Capturing an amount %s via Omise 3-D Secure payment.', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()))
                        );

                $order->addRelatedObject($invoice);
                break;

            default:
                $state_object->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW);
                $state_object->setStatus($order->getConfig()->getStateDefaultStatus(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW));
                $state_object->setIsNotified(false);

                return;

                break;
        }

        if ($charge->isAwaitPayment() || $charge->isAwaitCapture() || $charge->isSuccessful()) {
            $state_object->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
            $state_object->setStatus($order->getConfig()->getStateDefaultStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT));
            $state_object->setIsNotified(false);

            return;
        }

        $this->suspectToBeFailed($payment);
    }

    /**
     * @param  Varien_Object $payment
     * @param  float         $amount
     *
     * @return Omise_Gateway_Model_Api_Charge
     */
    public function processPayment(Varien_Object $payment, $amount)
    {
        $order = $payment->getOrder();

        return $this->process(
            $payment,
            array(
                'amount'      => $this->getAmountInSubunits($amount, $order->getOrderCurrencyCode()),
                'currency'    => $order->getOrderCurrencyCode(),
                'description' => 'Charge a card from Magento that order id is ' . $order->getIncrementId(),
                'capture'     => $this->isAutoCapture() ? true : false,
                'card'        => $payment->getAdditionalInformation('omise_token'),
                'return_uri'  => $this->getThreeDSecureCallbackUri(),
                'metadata'    => array(
                    'order_id' => $order->getIncrementId()
                )
            )
        );
    }

    /**
     * Authorize payment
     *
     * @param  Varien_Object $payment
     * @param  float         $amount
     *
     * @return self
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        Mage::log('Omise: authorizing payment.');

        $order  = $payment->getOrder();
        $charge = $this->process(
            $payment,
            array(
                'amount'      => $this->getAmountInSubunits($amount, $order->getOrderCurrencyCode()),
                'currency'    => $order->getOrderCurrencyCode(),
                'description' => 'Charge a card from Magento that order id is ' . $order->getIncrementId(),
                'capture'     => false,
                'card'        => $payment->getAdditionalInformation('omise_token'),
                'return_uri'  => ($this->isThreeDSecureNeeded() ? $this->getThreeDSecureCallbackUri() : null),
                'metadata'    => array(
                    'order_id' => $order->getIncrementId()
                )
            )
        );

        if ($charge->isAwaitPayment() || $charge->isAwaitCapture()) {
            $payment->setIsTransactionClosed(false);

            return $this;
        }

        $this->suspectToBeFailed($payment);
    }

    /**
     * Capture payment
     *
     * @param  Varien_Object $payment
     * @param  float         $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        Mage::log('Omise: capturing payment.');

        if ($charge_id = $payment->getAdditionalInformation('omise_charge_id')) {
            return $this->capture_manual($payment, $charge_id);
        }

        $order  = $payment->getOrder();
        $charge = $this->process(
            $payment,
            array(
                'amount'      => $this->getAmountInSubunits($amount, $order->getOrderCurrencyCode()),
                'currency'    => $order->getOrderCurrencyCode(),
                'description' => 'Charge a card from Magento that order id is ' . $order->getIncrementId(),
                'capture'     => true,
                'card'        => $payment->getAdditionalInformation('omise_token'),
                'return_uri'  => ($this->isThreeDSecureNeeded() ? $this->getThreeDSecureCallbackUri() : null),
                'metadata'    => array(
                    'order_id' => $order->getIncrementId()
                )
            )
        );

        if ($charge->isAwaitPayment() || $charge->isSuccessful()) {
            return $this;
        }

        $this->suspectToBeFailed($payment);
    }

    /**
     * Manual capture an authorized charge.
     *
     * @param  Varien_Object $payment
     * @param  string         $charge_id
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function capture_manual(Varien_Object $payment, $charge_id)
    {
        $charge = Mage::getModel('omise_gateway/api_charge')->find($charge_id);

        if (! $charge instanceof Omise_Gateway_Model_Api_Charge) {
            Mage::throwException(
                Mage::helper('payment')->__(
                    ($charge instanceof Omise_Gateway_Model_Api_Error) ? $charge->getMessage() : 'Payment failed. Note that your payment and order might (or might not) already has been processed. Please contact our support team to confirm your payment before resubmit.'
                )
            );
        }

        $charge->capture();

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

        if ($charge->isSuccessful()) {
            return $this;
        }

        $this->suspectToBeFailed($payment);
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

    /**
     * @return bool
     */
    public function isAutoCapture()
    {
        return $this->getConfigData('payment_action') === Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE;
    }
}
