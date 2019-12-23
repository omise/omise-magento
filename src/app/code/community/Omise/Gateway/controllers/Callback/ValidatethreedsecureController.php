<?php
class Omise_Gateway_Callback_ValidatethreedsecureController extends Omise_Gateway_Controller_Base
{
    const PAYMENT_TITLE = '3-D Secure';
    public function indexAction()
    {
        $this->order = $this->_getOrder();
        $this->setTitle(self::PAYMENT_TITLE);
        $this->setMessage(Mage::helper('omise_gateway')->__('Authorized amount of %s.', $this->order->getBaseCurrency()->formatTxt($this->order->getBaseTotalDue())));
        $this->setAwaitingOrderStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
        return $this->validate();
    }
}
