<?php
class Omise_Gateway_Block_Adminhtml_Dashboard_Dashboard extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('omise/dashboard.phtml');
    }

    public function getGetChargeUrl(){
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/omise/charges/', array('_secure'=>true));
    }

    public function getGetTransferUrl(){
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/omise/transfers/', array('_secure'=>true));
    }
}