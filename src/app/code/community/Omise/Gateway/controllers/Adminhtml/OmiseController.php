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
            // Retrieve Omise Account.
            $omise_account = Mage::getModel('omise_gateway/omiseaccount')->retrieveOmiseAccount();
            if (isset($omise_account['error']))
                throw new Exception('Omise Account:: '.$omise_account['error'], 1);

            // Retrieve Omise Balance.
            $omise_balance = Mage::getModel('omise_gateway/omisebalance')->retrieveOmiseBalance();
            if (isset($omise_balance['error']))
                throw new Exception('Omise Balance:: '.$omise_balance['error'], 1);

            // Retrieve Omise Transfer List.
            $omise_transfer = Mage::getModel('omise_gateway/omisetransfer')->retrieveOmiseTransfer(array(
                'limit' => 5
            ));
            if (isset($omise_transfer['error']))
                throw new Exception('Omise Transfer:: '.$omise_transfer['error'], 1);

            // Retrieve Omise Charge and Refund List.
            $omise_charge = Mage::getModel('omise_gateway/omisecharge')->retrieveOmiseCharges(array(
                'limit' => 5
            ));

            if (isset($omise_charge['error']))
                throw new Exception('Omise Charge:: '.$omise_charge['error'], 1);

            $data['omise'] = array(
                'email'     => $omise_account['email'],
                'created'   => $omise_account['created'],
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
                ),
                'charge'  => array(
                    'from'      => $omise_charge['from'],
                    'to'        => $omise_charge['to'],
                    'offset'    => $omise_charge['offset'],
                    'limit'     => $omise_charge['limit'],
                    'total'     => $omise_charge['total'],
                    'data'      => array_reverse($omise_charge['data'])
                )
            );
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();

            Mage::getSingleton('core/session')->addError($data['error']);
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
                if (!isset($post['test_mode']))
                    $post['test_mode'] = 0;

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

    /**
     * Withdraw
     * @return void
     */
    public function withdrawAction()
    {
        if (!Mage::app()->getRequest()->isPost() || (!$post = Mage::app()->getRequest()->getPost('OmiseTransfer'))) {
            Mage::getSingleton('core/session')->addError('Omise Transfer:: Required amount');
        } else {

            try {
                if (isset($post['action']) && $post['action'] == 'delete') {
                    // Delete action

                    $response = Mage::getModel('omise_gateway/omisetransfer')->deleteOmiseTransfer(Mage::app()->getRequest()->getParam('delete'));
                    if (isset($response['error']))
                        throw new Exception($response['error'], 1);
                    
                    $success = "Deleted";
                } else {
                    // Create action
                    $response = Mage::getModel('omise_gateway/omisetransfer')->createOmiseTransfer($post);
                    if (isset($response['error']))
                        throw new Exception($response['error'], 1);
                        
                    $success = "Transferred";
                }

                Mage::getSingleton('core/session')->addSuccess($success);
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError('Omise Transfer:: '.$e->getMessage());
            }
        }

        return $this->_redirect('adminhtml/omise/index', array());
    }

    /**
     * Transfers
     * @return void
     */
    public function transfersAction(){

        $omise_transfer = Mage::getModel('omise_gateway/omisetransfer')->retrieveOmiseTransfer(array(
            'limit' => 5,
            'page' => $this->getRequest()->getParams()['page']
        ));

        $result = array(
                'from'      => $omise_transfer['from'],
                'to'        => $omise_transfer['to'],
                'offset'    => $omise_transfer['offset'],
                'limit'     => $omise_transfer['limit'],
                'total'     => $omise_transfer['total'],
                'data'      => array_reverse($omise_transfer['data'])
            );

        foreach ($result['data'] as $key => $value) {
            $result['data'][$key]['amount'] = number_format(($value['amount']/100), 2);
            $date = new \DateTime($value['created']);
            $result['data'][$key]['created'] = $date->format('M d, Y H:i');
        }
        

        echo json_encode($result);
        return ;
    }

    /**
     * Charges
     * @return void
     */
    public function chargeAction(){
        
        $charge = $this->getRequest()->getParams()['charge'];
        $omise_charge = Mage::getModel('omise_gateway/omisecharge')->retrieveOmiseCharge($charge);

        $result = array(
            'id'      => $omise_charge['id'],
            'amount'     => $omise_charge['amount'],
            'refunded'     => $omise_charge['refunded'],
            'refunds'      => $omise_charge['refunds']
        );
        $result['amount_format'] = number_format(($result['amount']/100), 2);
        $result['refund_format'] = number_format(($result['refunded']/100), 2);
        foreach ($result['refunds']['data'] as $sub_key => $sub_value){
            $result['refunds']['data'][$sub_key]['refund_format'] = number_format(($sub_value['amount']/100), 2);
        }

        echo json_encode($result);
  
        return ;
    }

    /**
     * Charges
     * @return void
     */
    public function chargesAction(){

        $omise_charge = Mage::getModel('omise_gateway/omisecharge')->retrieveOmiseCharges(array(
            'limit' => 5,
            'page' => $this->getRequest()->getParams()['page']
        ));

        $result = array(
                'from'      => $omise_charge['from'],
                'to'        => $omise_charge['to'],
                'offset'    => $omise_charge['offset'],
                'limit'     => $omise_charge['limit'],
                'total'     => $omise_charge['total'],
                'data'      => array_reverse($omise_charge['data'])
            );

        foreach ($result['data'] as $key => $value) {
            $result['data'][$key]['amount_format'] = number_format(($value['amount']/100), 2);
            $refund = 0;
            foreach ($value['refunds']['data'] as $sub_key => $sub_value){
                $refund += $sub_value['amount'];
                $result['data'][$key]['refunds']['data'][$sub_key]['refund_format'] = number_format(($sub_value['amount']/100), 2);
            }
            
            if($refund!=0)
                $result['data'][$key]['refund_format'] = number_format(($refund/100), 2);

            $date = new \DateTime($value['created']);
            $result['data'][$key]['created'] = $date->format('M d, Y H:i');
        }
        

        echo json_encode($result);
  
        return ;
    }

    private function initiateRefund($charge){

        try{
            $payments = Mage::getResourceModel('sales/order_payment_collection')
                ->addFieldToSelect('*')
                ->addFieldToFilter('last_trans_id', $charge)
            ;

            $payment = $payments->getFirstItem();

            $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                ->addFieldToSelect('*')
                ->addFieldToFilter('transaction_id', $charge)
            ;

            $invoice = $invoices->getFirstItem();

            $orders = Mage::getResourceModel('sales/order_collection')
                ->addFieldToSelect('*')
                ->addFieldToFilter('entity_id', $payment->getParentId())
            ;

            $order = $orders->getFirstItem();

            return array(
                'charge' => $charge,
                'payment' => $payment,
                'invoice' => $invoice,
                'order' => $order
            );
        }catch(Exception $e){

        }

        return null;
    }

    /**
     * get requested invoice instance
     * @param unknown_type $order
     * @param String $invoiceId
     */
    private function getInvoice($order, $invoiceId)
    {
        if ($invoiceId) {
            $invoice = Mage::getModel('sales/order_invoice')
                ->load($invoiceId)
                ->setOrder($order);
            if ($invoice->getId()) {
                return $invoice;
            }
        }
        return false;
    }

    /**
     * get creditmemo model instance
     *
     * @return Mage_Sales_Model_Order_Creditmemo
     * @param unknown_type $order
     * @param String $invoiceId
     */
    private function getCreditmemo($orderId, $invoiceId){
        $order  = Mage::getModel('sales/order')->load($orderId);
        $invoice = $this->getInvoice($order, $invoiceId);
        $data = array();
        
        $service = Mage::getModel('sales/service_order', $order);
        if ($invoice) {
            $creditmemo = $service->prepareInvoiceCreditmemo($invoice, $data);
        } else {
            $creditmemo = $service->prepareCreditmemo($data);
        }

        Mage::register('current_creditmemo', $creditmemo);
        return $creditmemo;
    }

    /**
     * Save creditmemo and related order, invoice in one transaction
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    protected function saveCreditmemo($creditmemo)
    {
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($creditmemo)
            ->addObject($creditmemo->getOrder());
        if ($creditmemo->getInvoice()) {
            $transactionSave->addObject($creditmemo->getInvoice());
        }
        $transactionSave->save();

        return $this;
    }

    /**
     * get creditmemo model instance
     *
     * @return Mage_Sales_Model_Order_Creditmemo
     * @param String $charge
     */
    protected function refundMagento($data)
    {
        try {
            $creditmemo = $this->getCreditmemo($data['order']->getId(), $data['invoice']->getId());
            if ($creditmemo) {
                if (($creditmemo->getGrandTotal() <=0) && (!$creditmemo->getAllowZeroGrandTotal())) {
                    Mage::throwException(
                        $this->__('Credit memo\'s total must be positive.')
                    );
                }

                $comment = '';
                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );
                    if (isset($data['comment_customer_notify'])) {
                        $comment = $data['comment_text'];
                    }
                }

                if (isset($data['do_refund'])) {
                    $creditmemo->setRefundRequested(true);
                }
                if (isset($data['do_offline'])) {
                    $creditmemo->setOfflineRequested((bool)(int)$data['do_offline']);
                }

                $creditmemo->register();
       
                if (!empty($data['send_email'])) {
                    $creditmemo->setEmailSent(true);
                }

                $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $this->saveCreditmemo($creditmemo);
                $creditmemo->sendEmail(!empty($data['send_email']), $comment);
                Mage::getSingleton('adminhtml/session')->getCommentText(true);

                if (isset($refund['error'])){
                    Mage::throwException(Mage::helper('payment')->__('OmiseRefund:: '.$charge['error']));
                }else{
                    return array(
                        'amount' => $creditmemo->getGrandTotal()
                    );
                }
            }

        } catch (Mage_Core_Exception $e) {

        } catch (Exception $e) {
            Mage::logException($e);
        }


        return null;
    }

    /**
     * get creditmemo model instance
     *
     * @return Mage_Sales_Model_Order_Creditmemo
     * @param String $charge
     * @param Integer $amount
     */
    protected function refundOmise($charge, $amount){
        $refund = Mage::getModel('omise_gateway/omiserefund')->createOmiseRefund(array(
            "transaction_id" => $charge ,
            "amount"        => number_format($amount, 2, '', '')
        ));
    }


    /**
     * Refund
     * @return void
     */
    public function refundAction(){
        $charge = $this->getRequest()->getParams()['charge'];
        $amount = $this->getRequest()->getParams()['amount'];
        $partial = $this->getRequest()->getParams()['partial'] === 'true'? true: false;;

        $data = $this->initiateRefund($charge);

        if($data!=null){

            if(!$partial){
                $refunded = $this->refundMagento($data);
                $this->refundOmise($data['charge'], $amount/100);
                echo '{"refund_amount": "'. number_format(($amount/100), 2) .'"}'; 
            }else if(is_numeric($amount)){
                $this->refundOmise($data['charge'], $amount);
                echo '{"refund_amount": "'. number_format($amount, 2) .'"}';  
            }

        }
        
        return ;
    }

}