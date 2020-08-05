<?php
namespace Omise\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class OfflinePaymentObserver implements ObserverInterface
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

    /**
     * @var \Omise\Payment\Model\Api\Charge
     */
    protected $charge;
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @param \Omise\Payment\Helper\OmiseHelper $helper
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Omise\Payment\Model\Api\Charge $charge
     */
    public function __construct(
        \Omise\Payment\Helper\OmiseHelper $helper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Omise\Payment\Model\Api\Charge  $charge
    ) {
        $this->_scopeConfig      = $scopeConfig;
        $this->_helper           = $helper;
        $this->_transportBuilder = $transportBuilder;
        $this->_assetRepo = $assetRepo;
        $this->charge  = $charge;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $order   = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();
        $emailData     = new \Magento\Framework\DataObject();
        $charge_id = $payment->getAdditionalInformation('charge_id');
        $charge = $this->charge->find($charge_id);
        switch($payment->getAdditionalInformation('payment_type')) {
            case 'bill_payment_tesco_lotus':
                // make sure timezone is Thailand.
                date_default_timezone_set("Asia/Bangkok");
                $codeTemplate  = $this->_helper->convertTescoSVGCodeToHTML($payment->getAdditionalInformation('barcode'));
                $emailTemplate = 'send_email_tesco_template';
                $validUntil    = date("d-m-Y H:i:s" , strtotime($charge->expires_at));
                break;
            case 'paynow':
                // make sure timezone is Singapore.
                date_default_timezone_set("Asia/Singapore");
                $codeTemplate   = "<img src= '".$charge->source['scannable_code']['image']['download_uri']."'/>";
                $emailTemplate = 'send_email_paynow_template';
                $emailData->setData(['banksUrl' => $this->_assetRepo->getUrl('Omise_Payment::images/paynow_supportedbanks.png')]);
                $validUntil = $this->getPaynowChargeExpiryTime();
                break;
            case 'promptpay':
                // make sure timezone is Thailand.
                date_default_timezone_set("Asia/Bangkok");
                $codeTemplate   = $charge->source['scannable_code']['image']['download_uri'];
                $emailTemplate = 'send_email_promptpay_template';
                $validUntil    = date("d-m-Y H:i:s" , strtotime('+1 day'));
                break;
            default:
                return $this;
        }
        $paymentData   = $payment->getData();
        $amount        = number_format($paymentData['amount_ordered'], 2) . ' ' . $order->getOrderCurrency()->getCurrencyCode();
        $storeName     = $this->_scopeConfig->getValue('trans_email/ident_sales/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $storeEmail    = $this->_scopeConfig->getValue('trans_email/ident_sales/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $customerEmail = $order->getCustomerEmail();
        $orderId       = $order->getIncrementId();
        $emailData->setData(['barcode' => $codeTemplate, 'amount' => $amount, 'storename' => $storeName, 'orderId' => $orderId, 'validUntil' => $validUntil]);

        // build and send email
        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($emailTemplate)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            ])
            ->setTemplateVars(['data' => $emailData])
            ->setFrom([
                'name'  => $storeName,
                'email' => $storeEmail,
            ])
            ->addTo($customerEmail)
            ->getTransport();
        
        $transport->sendMessage();

        return $this;
    }

    /**
     * calculates expiry time for paynow charge
     * @return string
     */
    private function getPaynowChargeExpiryTime() {
        $timestamp = time() > strtotime('tomorrow - 1seconds') ? strtotime('tomorrow + 1day - 1second') : strtotime('tomorrow - 1seconds');
        return date("d-m-Y H:i:s" , $timestamp);
    }
}
