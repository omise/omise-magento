<?php
abstract class Omise_Gateway_Controller_Base extends Mage_Core_Controller_Front_Action
{
    protected function _construct()
    {
        $omise = Mage::getModel('omise_gateway/omise');
        $omise->initNecessaryConstant();
    }

    /**
     * @return \Omise_Gateway_Model_Order
     */
    protected function _getOrder()
    {
        $id = $this->getRequest()->getParam('order_id') ? $this->getRequest()->getParam('order_id') : null;

        return Mage::getModel('omise_gateway/order')->getOrder($id);
    }
}
