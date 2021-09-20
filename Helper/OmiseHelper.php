<?php
namespace Omise\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\HTTP\Header;
use SimpleXMLElement;
use DOMDocument;

class OmiseHelper extends AbstractHelper
{

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected $header;

    public function __construct(Header $header)
    {
        $this->header = $header;
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
     * This method checks and return TRUE if $paymentType is offline payment which is payable by image code
     * otherwise returns false.
     * @param string $paymentType
     * @return boolean
     */
    public function isPayableByImageCode($paymentType)
    {
        return (
            $paymentType === 'paynow'
            || $paymentType === 'promptpay'
            || $paymentType === 'bill_payment_tesco_lotus'
        );
    }

    /**
     * Check order payment processed using Omise payment methods.
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
        if ($charge->status === "pending"
        && !$charge->authorized
        && !$charge->paid
        && !empty($authorizeUri)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Get platform type of WEB, IOS or ANDROID to add to source API parameter.
     * @return string
     */
    public function getPlatformType()
    {
        $userAgent = $this->header->getHttpUserAgent();

        if ( preg_match( "/(Android)/i", $userAgent ) ) {
            return "ANDROID";
        }

        if ( preg_match( "/(iPad|iPhone|iPod)/i", $userAgent ) ) {
            return "IOS";
        }

        return "WEB";
    }

}
