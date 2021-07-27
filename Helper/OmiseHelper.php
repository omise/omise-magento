<?php
namespace Omise\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Omise\Payment\Model\Config\Internetbanking;
use Omise\Payment\Model\Config\Alipay;
use Omise\Payment\Model\Config\Pointsciti;
use Omise\Payment\Model\Config\Installment;
use Omise\Payment\Model\Config\Truemoney;
use Omise\Payment\Model\Config\Fpx;
use Omise\Payment\Model\Config\Paynow;
use Omise\Payment\Model\Config\Promptpay;
use Omise\Payment\Model\Config\Tesco;
use SimpleXMLElement;
use DOMDocument;

class OmiseHelper extends AbstractHelper
{
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
     * This method checks and return TRUE if $paymentMethod is offline payment which is payable by image code
     * otherwise returns false.
     * @param string $paymentMethod
     * @return boolean
     */
    public function isPayableByImageCode($paymentMethod)
    {
        return in_array(
            $paymentMethod,
            [
                Paynow::CODE,
                Promptpay::CODE,
                Tesco::CODE
            ]
        );
    }

    /**
     * This method checks and return TRUE if $paymentMethod is an offsite paymetn
     * otherwise returns false.
     * @param string $paymentMethod
     * @return boolean
     */
    public function isOffsitePayment($paymentMethod)
    {
       return in_array(
            $paymentMethod,
            [
                Alipay::CODE,
                Internetbanking::CODE,
                Installment::CODE,
                Truemoney::CODE,
                Pointsciti::CODE,
                Fpx::CODE
            ]
        );
    }

    /**
     * This method checks and return TRUE if $paymentType is offline or offsite
     * otherwise returns false.
     * @param string $paymentType
     * @return boolean
     */
    public function isOfflineOrOffsite($paymentMethod)
    {
        return $this->isPayableByImageCode($paymentMethod) || $this->isOffsitePayment($paymentMethod);
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
     * Checks if charge flow is direct (not offline or offsite).
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
}
