<?php
class Omise_Gateway_Model_PaymentMethod extends Mage_Payment_Model_Method_Cc
{
    /** @var string */
    protected $_code            = 'omise_gateway';
    /** @var string */
    protected $_formBlockType   = 'omise_gateway/form_cc';
    /** @var string */
    protected $_infoBlockType   = 'payment/info_cc';
    /**
     * Payment Method features
     * @var bool
     */
    protected $_isGateway       = true;
    protected $_canAuthorize    = true;
    protected $_canCapture      = true;
    protected $_canRefund       = true;
    /**
     * Authorize payment method
     * @param Varien_Object $payment
     * @param float $amount
     * @return Mage_Payment_Model_Abstract
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        Mage::log('Start authorize with OmiseCharge API!');
        
        $order = $payment->getOrder();
        $omise_token = $payment->getData('additional_information');
        $charge = Mage::getModel('omise_gateway/omisecharge')->createOmiseCharge(array(
            "amount"        => number_format($amount, 2, '', ''),
            "currency"      => strtolower($order->getBaseCurrencyCode()),
            "description"   => 'Charge a card from Magento that order id is '.$payment->getData('entity_id'),
            "capture"       => false,
            "card"          => $omise_token['omise_token']
        ));
        if (isset($charge['error']))
            Mage::throwException(Mage::helper('payment')->__('OmiseCharge:: '.$charge['error']));
        $this->getInfoInstance()->setAdditionalInformation('omise_charge_id', $charge['id']);
        Mage::log('This transaction was authorized! (by OmiseCharge API)');
        return $this;
    }
    /**
     * Capture payment method
     * @param Varien_Object $payment
     * @param float $amount
     * @return Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {   

        $order = $payment->getOrder();
        $authorized = isset($payment->getData('additional_information')['omise_charge_id']) ? $payment->getData('additional_information')['omise_charge_id'] : false;
        if ($authorized) {
            // Capture only.
            Mage::log('Start capture with OmiseCharge API!');
            $charge = Mage::getModel('omise_gateway/omisecharge')->captureOmiseCharge($authorized);
        } else {
            // Authorize and capture.
            Mage::log('Start capture with OmiseCharge API!');
            $omise_token = $payment->getData('additional_information');
            $charge = Mage::getModel('omise_gateway/omisecharge')->createOmiseCharge(array(
                "amount"        => number_format($amount, 2, '', ''),
                "currency"      => strtolower($order->getBaseCurrencyCode()),
                "description"   => 'Charge a card from Magento that order id is '.$payment->getData('entity_id'),
                "card"          => $omise_token['omise_token']
            ));
        }
        if (isset($charge['error']))
            Mage::throwException(Mage::helper('payment')->__('OmiseCharge:: '.$charge['error']));
        else{
            $this->getInfoInstance()->setAdditionalInformation('omise_charge_id', $charge['id']);
            $payment
                ->setTransactionId($charge['id'])
                ->setIsTransactionClosed(1);

            $order->setAdditionalInformation('omise_charge_id', $charge['id']); 
            $order->save();

            $tran = Mage::getModel('omise_gateway/transaction');
            $tran->setOrderId($charge['id']);
            $tran->setTransactionId($order->getId());
            $tran->save();
            
        }
        Mage::log('This transaction was authorized and captured! (by OmiseCharge API)');
        return $this;
    }
    /**
     * Refund payment method
     * @param Varien_Object $payment
     * @param float $amount
     * @return Mage_Payment_Model_Abstract
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $transactionId = $payment->getParentTransactionId();
        # request to refund
        $refund = Mage::getModel('omise_gateway/omiserefund')->createOmiseRefund(array(
            "transaction_id" => $transactionId,
            "amount"        => number_format($amount, 2, '', ''),
        ));
        if (isset($refund['error']))
            Mage::throwException(Mage::helper('payment')->__('OmiseRefund:: '.$charge['error']));
        else{
            $this->getInfoInstance()->setAdditionalInformation('omise_refund_id', $refund['id']);
            
            $payment
                ->setTransactionId($transactionId . '-' . Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND)
                ->setParentTransactionId($transactionId)
                ->setIsTransactionClosed(1)
                ->setShouldCloseParentTransaction(1);  
        }
         
        return $this;
    } 
    /**
     * Assign data to info model instance
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        Mage::log('Assign Data with Omise');
        $result = parent::assignData($data);
        if (is_array($data)) {
            if (!isset($data['omise_token']))
                Mage::throwException(Mage::helper('payment')->__('Need Omise\'s keys'));
            Mage::log('Data that assign is Array');
            $this->getInfoInstance()->setAdditionalInformation('omise_token', $data['omise_token']);
        } elseif ($data instanceof Varien_Object) {
            if (!$data->getData('omise_token'))
                Mage::throwException(Mage::helper('payment')->__('Need Omise\'s keys'));
            Mage::log('Data that assign is Object');
            $this->getInfoInstance()->setAdditionalInformation('omise_token', $data->getData('omise_token'));
        }
        return $result;
    }
}