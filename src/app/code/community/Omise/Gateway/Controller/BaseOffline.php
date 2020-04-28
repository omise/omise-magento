<?php
class Omise_Gateway_Controller_BaseOffline extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        return $this->renderLayout();
    }
}
