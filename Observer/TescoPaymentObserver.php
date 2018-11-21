<?php
namespace Omise\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class TescoPaymentObserver implements ObserverInterface
{
    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    private $_scopeConfig;    
    
    /**
    * @var  \Magento\Framework\Mail\Template\TransportBuilder
    */
    private $_transportBuilder;
    
    /**
    * @var  \Omise\Payment\Helper
    */
    private $_helper;

    public function __construct(
        \Omise\Payment\Helper\OmiseHelper $helper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig      = $scopeConfig;
        $this->_helper           = $helper;
        $this->_transportBuilder = $transportBuilder;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $order   = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();
        
        if (!$payment) {
            return $this;
        }

        $paymentData = $payment->getData();

        if ($paymentData['additional_information']['payment_type'] !== 'bill_payment_tesco_lotus') {
            return $this;
        }

        $amount        = number_format($paymentData['amount_ordered'], 2) . ' ' . $order->getOrderCurrency()->getCurrencyCode();
        $barcodeHtml   = $this->_helper->convertTescoSVGCodeToHTML($paymentData['additional_information']['barcode']);
        $storeName     = $this->_scopeConfig->getValue('trans_email/ident_sales/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $storeEmail    = $this->_scopeConfig->getValue('trans_email/ident_sales/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $customerEmail = $order->getCustomerEmail();
        $orderId       = $order->getIncrementId();

        // make sure timezone is Thailand.
        date_default_timezone_set("Asia/Bangkok");

        // get DateTime deadline that is in next 24 hours.
        $validUntil    = date("d-m-Y H:i:s" , time() + 24 * 60 * 60) . ' ICT';

        $emailData     = new \Magento\Framework\DataObject();
        $emailData->setData(['barcode' => $barcodeHtml, 'amount' => $amount, 'storename' => $storeName, 'orderId' => $orderId, 'valid' => $validUntil]);

        // build and send email
        $transport = $this->_transportBuilder
            ->setTemplateIdentifier('send_email_tesco_template')
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            ])
            ->setTemplateVars(['data' => $emailData])
            ->setFrom([
                'name'  => $storeName,
                'email' => $storeEmail,
            ])
            ->addTo(['email' => $customerEmail], \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->getTransport();
        
        $transport->sendMessage();

        return $this;
    }
}
