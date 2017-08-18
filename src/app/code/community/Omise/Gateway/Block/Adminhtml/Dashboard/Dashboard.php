<?php
class Omise_Gateway_Block_Adminhtml_Dashboard_Dashboard extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('omise/dashboard.phtml');
    }
}
