<?php
namespace Omise\Payment\Model\Data;

class Email
{
    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $_scopeConfig;

    /**
     * @var \Omise\Payment\Model\Api\Charge
     */
    protected $_charge;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
    * @var  \Magento\Framework\Mail\Template\TransportBuilder
    */
    private $_transportBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Omise\Payment\Helper\OmiseHelper
     */
    protected $_helper;

    /**
     * @var string
     */
    private $storeName;

    /**
     * @var string
     */
    private $emailTemplate;
    

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Omise\Payment\Model\Api\Charge  $charge,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Omise\Payment\Helper\OmiseHelper $helper
    ) {
        $this->_scopeConfig      = $scopeConfig;
        $this->_charge           = $charge;
        $this->_assetRepo        = $assetRepo;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager     = $storeManager;
        $this->_helper           = $helper;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    public function sendEmail($order) {
        $this->storeName  = $this->_scopeConfig->getValue('trans_email/ident_sales/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->storeEmail = $this->_scopeConfig->getValue('trans_email/ident_sales/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $emailData        = $this->getEmailData($order);
        $transport        = $this->_transportBuilder
            ->setTemplateIdentifier($this->emailTemplate)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId()
            ])
            ->setTemplateVars(['data' => $emailData])
            ->setFrom([
                'name'  => $this->storeName,
                'email' => $this->storeEmail
            ])
            ->addTo($order->getCustomerEmail())
            ->getTransport();
        $transport->sendMessage();
    }
    /**
     * @param \Magento\Sales\Model\Order $order
     * @return \Magento\Framework\DataObject
     */
    private function getEmailData($order) {
        $emailData   = new \Magento\Framework\DataObject();
        $payment     = $order->getPayment();
        $paymentType = $payment->getAdditionalInformation('payment_type');
        $charge      = $this->_charge->find($payment->getAdditionalInformation('charge_id'));
        switch($paymentType) {
            case 'bill_payment_tesco_lotus':
                // make sure timezone is Thailand.
                date_default_timezone_set("Asia/Bangkok");
                $emailData->addData(['barcode' => $this->_helper->convertTescoSVGCodeToHTML($payment->getAdditionalInformation('barcode'))]);
                $emailData->addData(['validUntil' => date("d-m-Y H:i:s" , strtotime($charge->expires_at))]);
                $this->emailTemplate = 'send_email_tesco_template';
                break;
            case 'paynow':
                // make sure timezone is Singapore.
                date_default_timezone_set("Asia/Singapore");
                $emailData->addData(['barcode' => "<img src= '".$charge->source['scannable_code']['image']['download_uri']."'/>"]);
                $emailData->addData(['banksUrl' => $this->_assetRepo->getUrl('Omise_Payment::images/paynow_supportedbanks.png')]);
                $emailData->addData(['validUntil' => $this->getPaynowChargeExpiryTime()]);
                $this->emailTemplate = 'send_email_paynow_template';
                break;
            case 'promptpay':
                // make sure timezone is Thailand.
                date_default_timezone_set("Asia/Bangkok");
                $emailData->addData(['barcode' => $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB)."omise/payment/complete/orderId/".$order->getIncrementId()]);
                $emailData->addData(['validUntil' => date("d-m-Y H:i:s" , strtotime('+1 day'))]);
                $this->emailTemplate = 'send_email_promptpay_template';
                break;
            default:
                return $this;
        }
        $paymentData = $payment->getData();
        $emailData->addData(['amount' => number_format($paymentData['amount_ordered'], 2) . ' ' . $order->getOrderCurrency()->getCurrencyCode()]);
        $emailData->addData(['orderId' => $order->getIncrementId()]);
        $emailData->addData(['storename' => $this->storeName]);
        return $emailData;
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
