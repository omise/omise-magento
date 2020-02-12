<?php 
class Omise_Gateway_Model_Payment_Offsitetesco extends Omise_Gateway_Model_Payment_SimpleOffsite_Payment {
    /**
     * @var string
     */
    protected $_code = 'omise_offsite_tesco';

    /**
     * @var string
     */
    protected $_formBlockType = 'omise_gateway/form_default';

    /**
     * @var string
     */
    protected $_infoBlockType = 'payment/info';

    /**
     * @var string
     */
    protected $_callbackUrl = 'omise/callback_validateoffsitetesco';

    /**
     * @var array
     */
    protected $_currencies = array('THB');

    /**
     * @var string
     */
    protected $_type = 'bill_payment_tesco_lotus';

    /**
     * Payment Method features
     *
     * @var bool
     */
    protected $_isGateway          = true;
    protected $_canReviewPayment   = true;
    protected $_isInitializeNeeded = true;

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
        $charge = $this->processPayment($payment, $invoice->getBaseGrandTotal());
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
                $state_object->setIsNotified(true);
                $this->sendOrderConfirmationEmail($order, $charge);
                $payment->setIsTransactionPending(true);
                Mage::getSingleton('checkout/session')->setOmiseAuthorizeUri(Mage::getUrl('omise/checkout_tesco', array()));
            }
            
            return;
        }
        $this->_suspectToBeFailed($payment);
    }

    /**
     * @param Varien_Object $payment
     * @param float $amount 
     *
     * @return Omise_Gateway_Model_Api_Charge
     * @throws Mage_Core_Exception
     */
    public function processPayment(Varien_Object $payment, $amount) {
        $order = $payment->getOrder();

        return parent::_process(
            $payment,
            array(
                'amount'      => $this->getAmountInSubunits($amount, $order->getBaseCurrencyCode()),
                'currency'    => $order->getBaseCurrencyCode(),
                'description' => 'Processing payment with '.$this->getPaymentTitle().'. Magento order ID: ' . $order->getIncrementId(),
                'source'      => array('type' => $this->_type),
                'return_uri'  => $this->getCallbackUri(),
                'metadata'    => array(
                    'order_id' => $order->getIncrementId()
                )
            )
        );
    }

    /**
     * Send Tesco Lotus Payment Barcode to customer.
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
        $mailer->setTemplateId((string) 'omise_gateway_email_tesco_orderconfirmation');
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
        $barcode = Mage::helper('omise_gateway')->tescoBarcodeSvgToHtml($charge);
        $barcode_ref = Mage::helper('omise_gateway')->generateTescoReference($charge);
        $store = Mage::app()->getStore();
        date_default_timezone_set(
            Mage::app()->getStore($store)->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)
        );
        $time = date('d-m-Y H:i:s', strtotime($charge->expires_at));
        $billingAddress = $order->getBillingAddress();
        if ($billingAddress->getEmail()) {
            $email = $billingAddress->getEmail();
            $customerName = $billingAddress->getFirstname()." ".$billingAddress->getLastname();
        } else {
            $email = $order->getCustomerEmail();
            $customerName = $order->getCustomerName();
        }
        return array(
            'orderid' => $order->getIncrementId(),
            'valid' => $time,
            'amount'=> number_format($order->getGrandTotal(), 2) . ' ' . $order->getOrderCurrencyCode(),
            'barcode' => $barcode,
            'barcode_ref' => $barcode_ref,
            'storename' => Mage::app()->getStore()->getFrontendName(),
            'customerName' => $customerName,
            'customerEmail' => $email
        );

        $storeId=Mage::app()->getStore()->getId();
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo((string)'mayur@omise.co',(string) 'Mayur Kathale');

        $mailer = Mage::getModel('core/email_template_mailer');
        $mailer->addEmailInfo($emailInfo);
        $mailer->setSender(array('email'=>(string) 'mayur.kathale@gmail.com','name'=> (string)'Mayur Kathale'));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId((string) 'omise_gateway_email_tesco_orderconfirmation');
        $mailer->setTemplateParams($data);
        $mailer->send();
    }
}