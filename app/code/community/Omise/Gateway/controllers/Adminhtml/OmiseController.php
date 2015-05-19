<?php

class Omise_Gateway_Adminhtml_OmiseController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init page.
     * @return self
     */
    protected function _initAction()
    {
        // Load layout, set active menu and breadcrumbs
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
        $data = array();

        // Retrieve Omise's data.
        try {
            $omise_services = Mage::getModel('omise_gateway/omise');

            // Retrieve Omise Account.
            $omise_account = $omise_services->retrieveOmiseAccount();
            if (isset($omise_account['error']))
                throw new Exception('Omise Account:: '.$omise_account['error'], 1);

            // Retrieve Omise Balance.
            $omise_balance = $omise_services->retrieveOmiseBalance();
            if (isset($omise_balance['error']))
                throw new Exception('Omise Balance:: '.$omise_balance['error'], 1);

            // Retrieve Omise Transfer List.
            $omise_transfer = $omise_services->retrieveOmiseTransfer();
            if (isset($omise_transfer['error']))
                throw new Exception('Omise Transfer:: '.$omise_transfer['error'], 1);

            
            $data['omise'] = array(
                'email'     => $omise_balance['email'],
                'created'   => $omise_balance['created'],
                'available' => $omise_balance['available'],
                'total'     => $omise_balance['total'],
                'currency'  => $omise_balance['currency'],
                'livemode'  => $omise_balance['livemode'],
                'transfer'  => array(
                    'from'      => $omise_transfer['from'],
                    'to'        => $omise_transfer['to'],
                    'offset'    => $omise_transfer['offset'],
                    'limit'     => $omise_transfer['limit'],
                    'total'     => $omise_transfer['total'],
                    'data'      => array_reverse($omise_transfer['data'])
                )
            );
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
        }

        $block = $this->getLayout()
                      ->createBlock('omise_gateway_adminhtml/dashboard_dashboard')
                      ->setData($data);

        $this->_title($this->__('Index Action'))
             ->_initAction()
             ->_addContent($block)
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