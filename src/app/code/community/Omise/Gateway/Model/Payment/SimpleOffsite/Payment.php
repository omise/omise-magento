<?php


class Omise_Gateway_Model_Payment_SimpleOffsite_Payment extends Omise_Gateway_Model_Payment_Base_Payment
{
    /**
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Omise_Gateway_Model_Api_Charge
     * @throws Mage_Core_Exception
     */
    public function process(Varien_Object $payment, $amount) {
        $order = $payment->getOrder();

        return parent::_process(
            $payment,
            array(
                'amount'      => $this->getAmountInSubunits($amount, $order->getBaseCurrencyCode()),
                'currency'    => $order->getBaseCurrencyCode(),
                'description' => 'Processing payment with '.$this->getPaymentTitle().'. Magento order ID: ' . $order->getIncrementId(),
                'source'      => array('type' => $this->_type),
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
}