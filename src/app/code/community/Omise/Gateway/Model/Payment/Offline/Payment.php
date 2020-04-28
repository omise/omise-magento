<?php


class Omise_Gateway_Model_Payment_Offline_Payment extends Omise_Gateway_Model_Payment_SimpleOffsite_Payment {

    protected $_allowRedirect      = false;
    protected $_sendCodebyEmail      = true;
    /**
     * Instantiate state and set it to state object
     *
     * @param string        $payment_action
     * @param Varien_Object $state_object
     */
    public function initialize($payment_action, $state_object) {
        $payment = $this->getInfoInstance();
        $order   = $payment->getOrder();
        $invoice = $order->prepareInvoice()->register();
        $charge = $this->process($payment, $invoice->getBaseGrandTotal());
        $payment->setCreatedInvoice($invoice)
            ->setIsTransactionClosed(false)
            ->setIsTransactionPending(true)
            ->addTransaction(
                Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
                $invoice,
                false,
                Mage::helper('omise_gateway')->__('Capturing an amount of %s via '.$this->getPaymentTitle().'.', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()))
            );
        $order->addRelatedObject($invoice);
        if ($charge->isAwaitPayment() || $charge->isAwaitCapture() || $charge->isSuccessful()) {
            $state_object->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
            $state_object->setStatus($order->getConfig()->getStateDefaultStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT));
            if($charge->isAwaitPayment() ) {
                if($this->_sendCodebyEmail) {
                    $state_object->setIsNotified(true);
                    $this->sendOrderConfirmationEmail($order, $charge);
                }
                $payment->setIsTransactionPending(true);
                Mage::getSingleton('checkout/session')->setOmiseAuthorizeUri(Mage::getUrl($this->_successUrl, array()));
            }
            
            return;
        }
        $this->_suspectToBeFailed($payment);
    }

    /**
     * Collecting email data and sending barcode to customer.
     * @param Mage_Sales_Model_Order $order
     * @param Omise_Gateway_Model_Api_Charge $charge
     * @return void
     */
    protected function sendOrderConfirmationEmail($order, $charge) {
        $data = $this->getEmailData($order, $charge);
        $storeId=Mage::app()->getStore()->getId();
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo((string) $data['customerEmail'], (string) $data['customerName']);
        $mailer = Mage::getModel('core/email_template_mailer');
        $mailer->addEmailInfo($emailInfo);
        $mailer->setSender(
            array(
                'email'=>(string) Mage::getStoreConfig('trans_email/ident_general/email'),
                'name'=> (string) Mage::getStoreConfig('trans_email/ident_general/name')
            )
        );
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId((string) $this->_emailTemplateId);
        $mailer->setTemplateParams($data);
        $mailer->send();
    }

    /**
     * Returns data related to order which has to set in Order confirmation email.
     *
     * @param Mage_Sales_Model_Order $order
     * @param Omise_Gateway_Model_Api_Charge $charge
     * @return array
     */
    protected function getEmailData($order, $charge) {
        if(isset($charge->source['scannable_code'])) {
            $barcode = $charge->source['scannable_code']['image']['download_uri'];
        } else {
            $barcode = Mage::helper('omise_gateway')->tescoBarcodeSvgToHtml($charge);
            $barcode_ref = Mage::helper('omise_gateway')->generateTescoReference($charge);
        }
        $store = Mage::app()->getStore();
        $mageTimezone = Mage::app()->getStore($store)->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
        date_default_timezone_set($mageTimezone);
        $time = date('d-m-Y H:i:s', strtotime($charge->expires_at));
        $billingAddress = $order->getBillingAddress();
        if ($billingAddress->getEmail()) {
            $email = $billingAddress->getEmail();
            $customerName = $billingAddress->getFirstname()." ".$billingAddress->getLastname();
        } else {
            $email = $order->getCustomerEmail();
            $customerName = $order->getCustomerName();
        }
        $data = array(
            'orderid' => $order->getIncrementId(),
            'valid' => $time,
            'amount'=> number_format($order->getGrandTotal(), 2) . ' ' . $order->getOrderCurrencyCode(),
            'barcode' => $barcode,
            'storename' => Mage::app()->getStore()->getFrontendName(),
            'customerName' => $customerName,
            'customerEmail' => $email
        );
        if(isset($barcode_ref)) {
            $data['barcode_ref'] = $barcode_ref;
        }
        return $data;
    }
}