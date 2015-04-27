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
    $edit_block = $this->getLayout()
                       ->createBlock('omise_gateway_adminhtml/config_edit');

    $this->_title($this->__('Config Action'))
         ->_initAction()
         ->_addContent($edit_block)
         ->renderLayout();
  }
}