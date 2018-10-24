<?php
namespace Omise\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Framework\Event\Observer;
class NewTescoPaymentObserver implements ObserverInterface
{
    /**
    * @var \Magento\Checkout\Model\Session
    */
    private $_checkoutSession;

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
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_transportBuilder = $transportBuilder;
    }


    /**
     * Set forced canCreditmemo flag
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->_checkoutSession->getLastRealOrder();
        $paymentData = $order->getPayment()->getData();

        if ($paymentData['additional_information']['payment_type'] !== 'bill_payment_tesco_lotus') {
            return $this;
        }

        $orderCurrency = $order->getOrderCurrency()->getCurrencyCode();

        $storeName =  $this->_scopeConfig->getValue(
            'trans_email/ident_sales/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        
        $storeEmail = $this->_scopeConfig->getValue(
            'trans_email/ident_sales/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $barcodeHtml = $this->_helper->convertTescoSVGCodeToHTML($paymentData['additional_information']['barcode']);

        $sender = [
            'name' => $storeName,
            'email' => $storeEmail,
        ];
        
        $emailData = new \Magento\Framework\DataObject();

        $emailData->setData(['barcode'=>$barcodeHtml]);
        
        $transport = $this->_transportBuilder
            ->setTemplateIdentifier('send_email_email_template')
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            ])
            ->setTemplateVars(['data' => $emailData])
            ->setFrom($sender)
            ->addTo([ 'email'=>'jacek.stanusz@gmail.com',], \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->getTransport();
        
        $transport->sendMessage();

        return $this;
    }
}
