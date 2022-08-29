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
use Omise\Payment\Model\Config\Fpx;
use Omise\Payment\Model\Config\Paynow;
use Omise\Payment\Model\Config\Promptpay;
use Omise\Payment\Model\Config\Tesco;
use Omise\Payment\Model\Config\Alipayplus;
use Omise\Payment\Model\Config\Mobilebanking;
use Omise\Payment\Model\Config\Rabbitlinepay;
use Omise\Payment\Model\Config\Ocbcpao;
use Omise\Payment\Model\Config\Grabpay;
use Omise\Payment\Model\Config\Cc;
use Omise\Payment\Model\Config\CcGooglePay;
use Omise\Payment\Model\Config\Conveniencestore;
use Omise\Payment\Model\Config\Config;

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
        Alipayplus::TOUCHNGO_CODE,
        Mobilebanking::CODE,
        Rabbitlinepay::CODE,
        Ocbcpao::CODE,
        Grabpay::CODE
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
     * @param Header $header
     * @param Config $config
     * @param Http $httpRequest
     */
    protected $config;

    public function __construct(
        Header $header,
        Config $config,
        Http $httpRequest
    ) {
        $this->header = $header;
        $this->config = $config;
        $this->httpRequest = $httpRequest;
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
     * Get platform type of WEB, IOS or ANDROID to add to source API parameter.
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

        if ($order->hasInvoices() && $this->config->getSendInvoiceAtOrderStatus() == Order::STATE_PENDING_PAYMENT) {
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
        $omisePaymentMethods = array_merge(
            $this->offsitePaymentMethods,
            $this->offlinePaymentMethods,
            $this->cardPaymentMethods
        );

        return in_array($paymentMethod, $omisePaymentMethods);
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

    /**
     * Validate whether the a URI was triggered by Omise server or not
     */
    public function isUserOriginated()
    {
        $fetchSite = $this->httpRequest->getServer('HTTP_SEC_FETCH_SITE');
        // "none" means the request is a user-originated operation
        return 'none' === $fetchSite;
    }
}
