<?php
class Omise_Gateway_Model_Payment_Offsiteinstalment extends Omise_Gateway_Model_Payment
{
    /**
     * @var string
     */
    protected $_code = 'omise_offsite_instalment';

    /**
     * @var string
     */
    protected $_formBlockType = 'omise_gateway/form_offsiteinstalment';

    /**
     * @var string
     */
    protected $_infoBlockType = 'payment/info';

    /**
     * Payment Method features
     *
     * @var bool
     */
    protected $_isGateway          = true;
    protected $_canReviewPayment   = true;
    protected $_isInitializeNeeded = true;

    
    /**
     * Get an array of instalment backends suitable for this transaction
     *
     * @return array
     */
    public function getValidBackends()
    {
        // TODO - we need to get some of the order details here in order to select appropriate
        // instalment backends to show the user. Need to look up how to get the details at this point

        //////$order = $this->getInfoInstance()->getOrder();
        return Mage::getModel('omise_gateway/api_capabilities')->getBackends(
            'installment',
            'THB', ////// $order->getBaseCurrencyCode(),
            20000 ////// $this->getAmountInSubunits($amount, $order->getBaseCurrencyCode())
        );
    }

    /**
     * Check method for processing with base currency
     * Note that Instalments can only be used with Omise Thailand account and 'THB' currency
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
     * Instantiate state and set it to state object
     *
     * @param string        $payment_action
     * @param Varien_Object $state_object
     */
    public function initialize($payment_action, $state_object)
    {
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
                    Mage::helper('omise_gateway')->__('Processing an amount of %s via Omise Instalment payment.', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()))
                );

        $order->addRelatedObject($invoice);

        if ($charge->isAwaitPayment()) {
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
    public function process(Varien_Object $payment, $amount)
    {
        $order = $payment->getOrder();

        return parent::process(
            $payment,
            array(
                'amount'      => $this->getAmountInSubunits($amount, $order->getBaseCurrencyCode()),
                'currency'    => $order->getBaseCurrencyCode(),
                'description' => 'Processing payment with instalments. Magento order ID: ' . $order->getIncrementId(),
                // 'offsite'     => 'instalment', ////// $payment->getAdditionalInformation('offsite'),
                'type'     => $payment->getAdditionalInformation('type'),
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
            'omise/callback_validateoffsiteinstalment',
            array(
                '_secure' => Mage::app()->getStore()->isCurrentlySecure(),
                '_query'  => $params
            )
        );
    }
}
