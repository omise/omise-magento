<?php

class Omise_Gateway_Adminhtml_OmiseController extends Mage_Adminhtml_Controller_Action
{
  protected function _initAction()
  {
    // load layout, set active menu and breadcrumbs
    $this->loadLayout()
         ->_setActiveMenu('omise');

    return $this;
  }

  public function indexAction()
  {
    $this->_title($this->__('Index Action'))
         ->_initAction()
         ->renderLayout();
  }

  public function configAction()
  {
    $this->_title($this->__('Index Action'))
         ->_initAction()
         ->renderLayout();
  }
}