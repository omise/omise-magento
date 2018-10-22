<?php
namespace Omise\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
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
                // Convert to a small unit
                $amount = $amount * 100;
                break;
        }

        return $amount;
    }


    public function convertSVGToHTML($svg)
    {
        return 'hahahahahah';
        $xml = new SimpleXMLElement($svg);
        if (!$xml)
        {
            return 'ERROR PARSING XML';
        }

        $node = $xml->children()->children();
        $xhtml = new DOMDocument();
        foreach ($node as $child)
        {
            if ($child->getName() === 'g') {
                $prevX = 0;
                $prevWidth = 0;
                
                foreach ($child->children() as $rect) {
                    $attrArr = $rect->attributes();
                    $divRect = $xhtml->createElement('div');
                    $width = $attrArr['width'];
                    $margin =($attrArr['x'] - $prevX - $prevWidth) . 'px';
                    $divRect->setAttribute('style', "float:left;position:relative;height:50px; width:$width; background-color:#000; margin-left:$margin");
                    $xhtml->appendChild($divRect);
                    $prevX = $attrArr['x'];
                    $prevWidth = $attrArr['width'];
                }
            }
        }
        return $xhtml->saveXML(null, LIBXML_NOEMPTYTAG);
    }
}
