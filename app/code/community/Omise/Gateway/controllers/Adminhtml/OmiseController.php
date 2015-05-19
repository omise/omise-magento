<?php

class Omise_Gateway_Adminhtml_OmiseController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init page.
     * @return self
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
             ->_setActiveMenu('omise');

        return $this;
    }

    /**
     * Index page
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('Index Action'))
             ->_initAction()
             ->renderLayout();
    }

    /**
     * Config page
     * @return void
     */
    public function configAction()
    {
        // Create a new model instance and query data from 'omise_gateway' table.
        $config = Mage::getModel('omise_gateway/config')->load(1);

        // process a submit form if it was submitted.
        if ($post = $this->getRequest()->getPost('configData')) {
            try {
                $config->addData($post);
                $config->save();

                $this->_getSession()->addSuccess($this->__('The config has been saved.'));
                return $this->_redirect('adminhtml/omise/config/edit', array());
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            }
        }

        // Make the current value object available to blocks.
        Mage::register('current_value', $config);


        $edit_block = $this->getLayout()
                           ->createBlock('omise_gateway_adminhtml/config_edit');

        $this->_title($this->__('Config Action'))
             ->_initAction()
             ->_addContent($edit_block)
             ->renderLayout();
    }
}