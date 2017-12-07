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
    protected $_isGateway          = true;
    protected $_canReviewPayment   = true;
    protected $_isInitializeNeeded = true;

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
                    Mage::helper('omise_gateway')->__('Processing amount %s via Omise Internet Banking payment.', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()))
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
                'amount'      => $this->getAmountInSubunits($amount, $order->getOrderCurrencyCode()),
                'currency'    => $order->getOrderCurrencyCode(),
                'description' => 'Processing payment with Internet Banking. Magento order ID: ' . $order->getIncrementId(),
                'offsite'     => $payment->getAdditionalInformation('offsite'),
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
            'omise/callback_validateoffsiteinternetbanking',
            array(
                '_secure' => Mage::app()->getStore()->isCurrentlySecure(),
                '_query'  => $params
            )
        );
    }
}
