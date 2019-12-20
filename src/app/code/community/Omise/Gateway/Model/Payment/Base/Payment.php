<?php


class Omise_Gateway_Model_Payment_Base_Payment extends Omise_Gateway_Model_Payment {
    /**
     * @var string
     */
    protected $_code;

    /**
     * @var string
     */
    protected $_formBlockType;

    /**
     * @var string
     */
    protected $_infoBlockType;

    /**
     * Payment Method features
     *
     * @var bool
     */
    protected $_isGateway;

    /**
     * Payment Method features
     *
     * @var bool
     */
    protected $_canReviewPayment;

    /**
     * Payment Method features
     *
     * @var bool
     */
    protected $_isInitializeNeeded;

    /**
     * @var string
     */
    protected $_callbackUrl;

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->_code = $code;
    }

    /**
     * @param string $formBlockType
     */
    public function setFormBlockType($formBlockType)
    {
        $this->_formBlockType = $formBlockType;
    }

    /**
     * @param string $infoBlockType
     */
    public function setInfoBlockType($infoBlockType)
    {
        $this->_infoBlockType = $infoBlockType;
    }

    /**
     * @param bool $isGateway
     */
    public function setIsGateway($isGateway)
    {
        $this->_isGateway = $isGateway;
    }

    /**
     * @param bool $canReviewPayment
     */
    public function setCanReviewPayment($canReviewPayment)
    {
        $this->_canReviewPayment = $canReviewPayment;
    }

    /**
     * @param bool $isInitializeNeeded
     */
    public function setIsInitializeNeeded($isInitializeNeeded)
    {
        $this->_isInitializeNeeded = $isInitializeNeeded;
    }

    /**
     * @param string $callbackUrl
     */
    public function setCallbackUrl($callbackUrl)
    {
        $this->_callbackUrl = $callbackUrl;
    }

    /**
     * @return string
     */
    public function getCallbackUrl()
    {
        return $this->_callbackUrl;
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string        $payment_action
     * @param Varien_Object $state_object
     */
    public function initialize($payment_action, $state_object)
    {
        $title =  (string)Mage::getConfig()->getNode('default/payment/'.$this->getCode().'/title');
        $payment = $this->getInfoInstance();
        $order   = $payment->getOrder();

        $invoice = $order->prepareInvoice();
        $invoice->setIsPaid(false)->register();

        $charge = $this->process($payment, $invoice->getBaseGrandTotal());

        $payment->setCreatedInvoice($invoice)
            ->setIsTransactionClosed(false)
            ->setIsTransactionPending(true)
            ->addTransaction(
                Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER,
                $invoice,
                false,
                Mage::helper('omise_gateway')->__('Processing an amount of %s via Omise '.$title.' payment.', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()))
            );

        $order->addRelatedObject($invoice);

        if ($charge->isAwaitPayment()) {
            $state_object->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
            $state_object->setStatus($order->getConfig()->getStateDefaultStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT));
            $state_object->setIsNotified(false);

            return;
        }

        $this->_suspectToBeFailed($payment);
    }

    /**
     * Check method for processing with base currency
     * Note that Internet Banking can only be used with Omise Thailand account and 'THB' currency
     *
     * @param  string $currencyCode
     *
     * @return boolean
     */
    public function canUseForCurrency($currencyCode)
    {
        return (strtoupper($currencyCode) === 'THB' && strtoupper(Mage::app()->getStore()->getCurrentCurrencyCode()) === 'THB');
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
            $this->getCallbackUrl(),
            array(
                '_secure' => Mage::app()->getStore()->isCurrentlySecure(),
                '_query'  => $params
            )
        );
    }
}