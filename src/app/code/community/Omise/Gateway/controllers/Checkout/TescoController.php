<?php
class Omise_Gateway_Checkout_TescoController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        return $this->renderLayout();
    }
}
