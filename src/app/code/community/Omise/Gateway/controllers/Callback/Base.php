<?php
abstract class Omise_Gateway_Controllers_Callback_Base extends Mage_Core_Controller_Front_Action
{
    protected function _construct()
    {
        $omise = Mage::getModel('omise_gateway/omise');
        $omise->initNecessaryConstant();
    }

    /**
     * @return \Mage_Sales_Model_Order
     */
    protected function getOrder()
    {
        if ($this->getRequest()->getParam('order_id')) {
            return Mage::getModel('sales/order')->loadByIncrementId($this->getRequest()->getParam('order_id'));
        }

        return Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId());
    }

    /**
     * @param  \Mage_Sales_Model_Order $order
     * @param  string                  $message
     *
     * @return self
     */
    protected function markOrderAsFailed($order, $message)
    {
        $order->getPayment()
            ->setPreparedMessage($message)
            ->deny();

        $order->save();

        Mage::getSingleton('core/session')->addError($message);

        return $this->_redirect('checkout/cart');
    }
}
