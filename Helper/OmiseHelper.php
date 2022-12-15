<?php
namespace Omise\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\HTTP\Header;
use Magento\Sales\Model\Order;
use Magento\Framework\App\Request\Http;

use Omise\Payment\Model\Config\Internetbanking;
use Omise\Payment\Model\Config\Alipay;
use Omise\Payment\Model\Config\Pointsciti;
use Omise\Payment\Model\Config\Installment;
use Omise\Payment\Model\Config\Truemoney;
use Omise\Payment\Model\Config\Touchngo;
use Omise\Payment\Model\Config\Fpx;
use Omise\Payment\Model\Config\Paynow;
use Omise\Payment\Model\Config\Promptpay;
use Omise\Payment\Model\Config\Tesco;
use Omise\Payment\Model\Config\Alipayplus;
use Omise\Payment\Model\Config\Mobilebanking;
use Omise\Payment\Model\Config\Rabbitlinepay;
use Omise\Payment\Model\Config\Ocbcpao;
use Omise\Payment\Model\Config\Grabpay;
use Omise\Payment\Model\Config\Boost;
use Omise\Payment\Model\Config\DuitnowOBW;
use Omise\Payment\Model\Config\DuitnowQR;
use Omise\Payment\Model\Config\MaybankQR;
use Omise\Payment\Model\Config\Shopeepay;
use Omise\Payment\Model\Config\Cc;
use Omise\Payment\Model\Config\CcGooglePay;
use Omise\Payment\Model\Config\Conveniencestore;
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Model\Config\ShopeepayJumpApp;

use SimpleXMLElement;
use DOMDocument;

class OmiseHelper extends AbstractHelper
{
    /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected $header;

    /**
     * @var array
     */
    private $offsitePaymentMethods = [
        Alipay::CODE,
        Internetbanking::CODE,
        Installment::CODE,
        Truemoney::CODE,
        Pointsciti::CODE,
        Fpx::CODE,
        Alipayplus::ALIPAY_CODE,
        Alipayplus::ALIPAYHK_CODE,
        Alipayplus::DANA_CODE,
        Alipayplus::GCASH_CODE,
        Alipayplus::KAKAOPAY_CODE,
        Touchngo::CODE,
        Mobilebanking::CODE,
        Rabbitlinepay::CODE,
        Ocbcpao::CODE,
        Grabpay::CODE,
        Boost::CODE,
        DuitnowOBW::CODE,
        DuitnowQR::CODE,
        MaybankQR::CODE,
        Shopeepay::CODE
    ];

    /**
     * Payment method payable by image code
     *
     * @var array
     */
    private $imageCodePaymentMethods = [
        Paynow::CODE,
        Promptpay::CODE,
        Tesco::CODE
    ];

    /**
     * @var array
     */
    private $offlinePaymentMethods = [
        Paynow::CODE,
        Promptpay::CODE,
        Tesco::CODE,
        Conveniencestore::CODE
    ];

    /**
     * @var array
     */
    private $cardPaymentMethods = [
        Cc::CODE,
        CcGooglePay::CODE
    ];

    /**
     * @var array
     */
    private $omisePaymentMethods;

    /**
     *
     * @var array
     */
    private $omiseCodeByOmiseId = [
        // card payment
        Cc::ID => Cc::CODE,
        CcGooglePay::ID => CcGooglePay::CODE,

        // offsite payment
        Alipay::ID => Alipay::CODE,
        Truemoney::ID => Truemoney::CODE,
        Pointsciti::ID => Pointsciti::CODE,
        Fpx::ID => Fpx::CODE,
        Alipayplus::ALIPAY_ID => Alipayplus::ALIPAY_CODE,
        Alipayplus::ALIPAYHK_ID => Alipayplus::ALIPAYHK_CODE,
        Alipayplus::DANA_ID => Alipayplus::DANA_CODE,
        Alipayplus::GCASH_ID => Alipayplus::GCASH_CODE,
        Alipayplus::KAKAOPAY_ID => Alipayplus::KAKAOPAY_CODE,
        Touchngo::ID => Touchngo::CODE,
        Rabbitlinepay::ID => Rabbitlinepay::CODE,
        Ocbcpao::ID => Ocbcpao::CODE,
        Grabpay::ID => Grabpay::CODE,
        Boost::ID => Boost::CODE,
        DuitnowOBW::ID => DuitnowOBW::CODE,
        DuitnowQR::ID => DuitnowQR::CODE,
        MaybankQR::ID => MaybankQR::CODE,
        Shopeepay::ID => Shopeepay::CODE,
        ShopeepayJumpApp::ID => ShopeepayJumpApp::CODE,

        // offsite internet banking payment
        Internetbanking::BBL_ID => Internetbanking::CODE,
        Internetbanking::BAY_ID => Internetbanking::CODE,
        Internetbanking::KTB_ID => Internetbanking::CODE,
        Internetbanking::SCB_ID => Internetbanking::CODE,

        // offsite installment banking payment
        Installment::BAY_ID => Installment::CODE,
        Installment::BBL_ID => Installment::CODE,
        Installment::CITI_ID => Installment::CODE,
        Installment::UOB_ID => Installment::CODE,
        Installment::FIRST_CHOICE_ID => Installment::CODE,
        Installment::KBANK_ID => Installment::CODE,
        Installment::KTC_ID => Installment::CODE,
        Installment::SCB_ID => Installment::CODE,
        Installment::TTB_ID => Installment::CODE,
        Installment::UOB_ID => Installment::CODE,
        Installment::MBB_ID => Installment::CODE,

        // offsite installment banking payment
        Mobilebanking::BAY_ID => Mobilebanking::CODE,
        Mobilebanking::BBL_ID => Mobilebanking::CODE,
        Mobilebanking::KBANK_ID => Mobilebanking::CODE,
        Mobilebanking::SCB_ID => Mobilebanking::CODE,

        // offline payment
        Paynow::ID => Paynow::CODE,
        Promptpay::ID => Promptpay::CODE,
        Tesco::ID => Tesco::CODE,
        Conveniencestore::ID => Conveniencestore::CODE
    ];

    /**
     *
     * @var array
     */
    private $labelByOmiseCode = [
        // card payment
        Cc::CODE => "Credit Card Payment",
        CcGooglePay::CODE => "Google Pay Payment",

        // offsite payment
        Alipay::CODE => "Alipay",
        Internetbanking::CODE => "Internet Banking Payment",
        Installment::CODE => "Installment Payment",
        Truemoney::CODE => "TrueMoney Wallet Payment",
        Pointsciti::CODE => "Citi Pay with Points",
        Fpx::CODE => "FPX Payment",
        Alipayplus::ALIPAY_CODE => "Alipay (Alipay+ Partner) Payment",
        Alipayplus::ALIPAYHK_CODE => "AlipayHK (Alipay+ Partner) Payment",
        Alipayplus::DANA_CODE => "DANA (Alipay+ Partner) Payment",
        Alipayplus::GCASH_CODE => "GCash (Alipay+ Partner) Payment",
        Alipayplus::KAKAOPAY_CODE => "Kakao Pay (Alipay+ Partner) Payment",
        Touchngo::CODE => "Touch`n Go eWallet Payment",
        Mobilebanking::CODE => "Mobile Banking Payment",
        Rabbitlinepay::CODE => "Rabbit LINE Pay Payment",
        Ocbcpao::CODE => "OCBC Pay Anyone Payment",
        Grabpay::CODE => "GrabPay Payment",
        Boost::CODE => "Boost Payment",
        DuitnowOBW::CODE => "DuitNow Online Banking/Wallets Payment",
        DuitnowQR::CODE => "DuitNow QR Payment",
        MaybankQR::CODE => "Maybank QRPay Payment",
        Shopeepay::CODE => "ShopeePay Payment",

        // offline payment
        Paynow::CODE => "PayNow QR Payment",
        Promptpay::CODE => "PromptPay QR Payment",
        Tesco::CODE => "Tesco Lotus Bill Payment",
        Conveniencestore::CODE => "Convenience Store Payment"
    ];

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Header $header
     * @param Config $config
     * @param Http $httpRequest
     */
    public function __construct(
        Header $header,
        Config $config,
        Http $httpRequest
    ) {
        $this->header = $header;
        $this->config = $config;
        $this->httpRequest = $httpRequest;

        $this->omisePaymentMethods = array_merge(
            $this->offsitePaymentMethods,
            $this->offlinePaymentMethods,
            $this->cardPaymentMethods
        );
    }

    /**
     * @param  string $fieldId
     *
     * @return string
     */
    public function getConfig($fieldId)
    {
        $path = 'payment/omise/' . $fieldId;

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param  string  $currency
     * @param  integer $amount
     *
     * @return string
     */
    public function omiseAmountFormat($currency, $amount)
    {
        switch (strtoupper($currency)) {
            case 'EUR':
            case 'GBP':
            case 'SGD':
            case 'THB':
            case 'USD':
            case 'AUD':
            case 'CAD':
            case 'CHF':
            case 'CNY':
            case 'DKK':
            case 'HKD':
            case 'MYR':
                // Convert to a small unit
                $amount *= 100;
                break;
        }

        return $amount;
    }

    /**
     * Convert tesco code returned from Omise Backend in SVG format to HTML format
     *
     * @param  string  $svg
     *
     * @return string
     */
    public function convertTescoSVGCodeToHTML($svg)
    {
        // remove from $svg unnecessary elements

        // find first "<rect" node element
        // delete everything until first "<rect"
        $svg = substr($svg, strpos($svg, '<rect'));

        //find last </g> closing tag and cut everything after it
        $svg = substr($svg, 0, strpos($svg, '</g>') + strlen('</g>'));

        //insert everything into master tag (requirement of SimpleXMLElement class)
        $svg = '<svg>' . $svg . '</svg>';
        $xml = new SimpleXMLElement($svg);
        if (!$xml) {
            return;
        }

        //get first children
        $node = $xml->children();

        //initialize return value
        $xhtml = new DOMDocument();

        //analyze svg nodes, and generate html
        foreach ($node as $child) {
            // all rect nodes are in group master node
            if ($child->getName() === 'g') {
                $prevX = 0;
                $prevWidth = 0;

                // get data from all rect nodes
                foreach ($child->children() as $rect) {
                    $attrArr = $rect->attributes();
                    $divRect = $xhtml->createElement('div');
                    $width   = $attrArr['width'];
                    $margin  = ($attrArr['x'] - $prevX - $prevWidth) . 'px';

                    //set html attributes based on SVG attributes
                    $divRect->setAttribute(
                        'style',
                        "float:left;position:relative; height:50px; width:$width; 
                        background-color:#000; margin-left:$margin"
                    );
                    $xhtml->appendChild($divRect);

                    $prevX = $attrArr['x'];
                    $prevWidth = $attrArr['width'];
                }
                // add empty div tag to clear 'float' css property
                $div = $xhtml->createElement('div');
                $div->setAttribute('style', "clear:both");
                $xhtml->appendChild($div);
            }
        }
        return $xhtml->saveXML(null, LIBXML_NOEMPTYTAG);
    }

    /**
     * Check if payment method is among the payment methods which is payable by image code or not
     *
     * @param string $paymentMethod
     * @return boolean
     */
    public function isPayableByImageCode($paymentMethod)
    {
        return in_array($paymentMethod, $this->imageCodePaymentMethods);
    }

    /**
     * Check if payment method is one of the offline payment methods or not
     *
     * @param string $paymentMethod
     * @return boolean
     */
    public function isOfflinePaymentMethod($paymentMethod)
    {
        return in_array($paymentMethod, $this->offlinePaymentMethods);
    }

    /**
     * Check if payment method is one of the offsite payment methods or not
     *
     * @param string $paymentMethod
     * @return boolean
     */
    public function isOffsitePaymentMethod($paymentMethod)
    {
        return in_array($paymentMethod, $this->offsitePaymentMethods);
    }

    /**
     * Check order payment processed using Omise payment methods.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return boolean
     */
    public function isOrderOmisePayment($order)
    {
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $methodCode = $method->getCode();
        return strpos($methodCode, "omise") > -1;
    }

    /**
     * Function to add extra filter in future according to order status.
     * @param \Magento\Sales\Model\Order $order
     * @return boolean
     */
    public function canOrderStatusAutoSync($order)
    {
        return $this->isOrderOmisePayment($order);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void|string
     */
    public function getOrderChargeId($order)
    {
        if ($this->isOrderOmisePayment($order)) {
            return $order->getPayment()->getAdditionalInformation('charge_id');
        }
    }

    /**
     * Checks if 3d secured setting enabled from charge data.
     * @param \Omise\Payment\Model\Api\Charge $charge
     * @return boolean
     */
    public function is3DSecureEnabled($charge)
    {
        $authorizeUri = $charge->authorize_uri;
        $notAuthorizedNorPaid = !$charge->authorized && !$charge->paid;
        $isPending = $charge->status === "pending";

        if ($isPending && $notAuthorizedNorPaid && !empty($authorizeUri)) {
            return true;
        }

        return false;
    }

    /**
     * Get platform Type of WEB, IOS or ANDROID to add to source API parameter.
     * @return string
     */
    public function getPlatformType()
    {
        $userAgent = $this->header->getHttpUserAgent();

        if (preg_match("/(Android)/i", $userAgent)) {
            return "ANDROID";
        }

        if (preg_match("/(iPad|iPhone|iPod)/i", $userAgent)) {
            return "IOS";
        }

        return "WEB";
    }

    /**
     * Check if current platform is mobile or not
     */
    public function isMobilePlatform()
    {
        return true;
        return 'WEB' !== $this->getPlatformType();
    }

    /**
     * Depending on the setting of state to generate invoice, we will either create an invoice or return a created one.
     * Invoice will be marked as successfully paid and returned.
     * @param \Magento\Sales\Model\Order order
     * @param int $chargeId
     * @param boolean $isCapture
     * @return Magento\Sales\Model\Order\Invoice
     */
    public function createInvoiceAndMarkAsPaid($order, $chargeId, $isCapture = true)
    {
        if (!$isCapture) {
            return;
        }

        $isOrderStatusPending = $this->config->getSendInvoiceAtOrderStatus() === Order::STATE_PENDING_PAYMENT;

        if ($order->hasInvoices() && $isOrderStatusPending) {
            $invoice = $order->getInvoiceCollection()->getLastItem();
        } else {
            $invoice = $order->prepareInvoice();
            $invoice->register();
            $order->addRelatedObject($invoice)->save();
        }

        $invoice->setTransactionId($chargeId)->pay()->save();
        return $invoice;
    }

    /**
     * This method checks and return TRUE if $paymentMethod is
     * offered by our Omise payment plugin otherwise returns false.
     *
     * @param string $paymentMethod
     *
     * @return boolean
     */
    public function isOmisePayment($paymentMethod)
    {
        return in_array($paymentMethod, $this->omisePaymentMethods);
    }

    /**
     * Return TRUE if $paymentMethod uses card token to create a charge.
     *
     * @param string $paymentMethod
     *
     * @return boolean
     */
    public function isCreditCardPaymentMethod($paymentMethod)
    {
        return in_array($paymentMethod, $this->cardPaymentMethods);
    }

    public function getOmiseLabelByOmiseCode(string $code)
    {
        if (array_key_exists($code, $this->labelByOmiseCode)) {
            return $this->labelByOmiseCode[$code];
        }
        return null;
    }

    public function getOmiseCodeByOmiseId(string $name)
    {
        if (array_key_exists($name, $this->omiseCodeByOmiseId)) {
            return $this->omiseCodeByOmiseId[$name];
        }
        return null;
    }
}
