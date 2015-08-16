<?php
class Omise_Gateway_Block_Adminhtml_Dashboard_Dashboard extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('omise/dashboard.phtml');
    }

    public function getChargeUrl(){
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/omise/charge/', array('_secure'=>true));
    }

    public function getChargesUrl(){
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/omise/charges/', array('_secure'=>true));
    }

    public function getTransfersUrl(){
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/omise/transfers/', array('_secure'=>true));
    }

    public function getOrderUrl(){
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/sales_order/view/order_id/:orderid', array('_secure'=>true));
    }
}