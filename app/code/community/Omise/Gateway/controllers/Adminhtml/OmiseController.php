<?php

class Omise_Gateway_Adminhtml_OmiseController extends Mage_Adminhtml_Controller_Action
{
  public function indexAction()
  {
    $this->loadLayout()
         ->_setActiveMenu('omise')
         ->_title($this->__('Index Action'));

    // my stuff
    $this->renderLayout();
  }

  public function configAction()
  {
    $this->loadLayout()
         ->_setActiveMenu('omise')
         ->_title($this->__('Index Action'));

    // my stuff
    $this->renderLayout();
  }
}