<?php


class Omise_Gateway_Model_Payment_Base_Payment extends Omise_Gateway_Model_Payment {
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
                Mage::helper('omise_gateway')->__('Processing an amount of %s via Omise '.$this->getPaymentTitle().' payment.', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()))
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
     *
     * @param  string $currencyCode
     *
     * @return boolean
     */
    public function canUseForCurrency($currencyCode)
    {
        if(isset($this->_currencies))
            return (
                in_array($currencyCode, $this->_currencies)
                && in_array(Mage::app()->getStore()->getCurrentCurrencyCode(), $this->_currencies)
            );
        else
            return true;
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
            $this->_callbackUrl,
            array(
                '_secure' => Mage::app()->getStore()->isCurrentlySecure(),
                '_query'  => $params
            )
        );
    }

    /**
     * Returns payment method title from config.xml
     * @return string
     */
    protected function getPaymentTitle() {
        return (string)Mage::getConfig()->getNode('default/payment/'.$this->getCode().'/title');
    }
}