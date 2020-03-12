<?php
class Omise_Gateway_Helper_Data extends Mage_Core_Helper_Abstract
{

    private static
        $currencyDecimals = [
            "BIF" => 0,
            "CLP" => 0,
            "DJF" => 0,
            "GNF" => 0,
            "ISK" => 0,
            "JPY" => 0,
            "KMF" => 0,
            "KRW" => 0,
            "PYG" => 0,
            "RWF" => 0,
            "UGX" => 0,
            "UYI" => 0,
            "VND" => 0,
            "VUV" => 0,
            "XAF" => 0,
            "XOF" => 0,
            "XPF" => 0,
            "BHD" => 3,
            "IQD" => 3,
            "JOD" => 3,
            "KWD" => 3,
            "LYD" => 3,
            "OMR" => 3,
            "TND" => 3,
        ],
        $defaultCurrencyDecimals = 2,
        $supportedCurrencies = [
            'AUD',
            'CAD',
            'CHF',
            'CNY',
            'DKK',
            'EUR',
            'GBP',
            'HKD',
            'JPY',
            'MYR',
            'THB',
            'SGD',
            'USD'
        ]
    ;


    /**
     * Return number of subunits the passed currency has
     *
     * @param string $currency
     * @return integer
     */
    public static function subUnitsFor($curr) {
        $c = strtoupper($curr);
        $decimals = isset(self::$currencyDecimals[$c]) ? self::$currencyDecimals[$c] : self::$defaultCurrencyDecimals;
        return pow(10, $decimals);
    }        

    /**
     *
     * @param string $currency
     * @param numeric $amount
     * @return string
     */
    public function amountToSubunits($currency, $amount)
    {
        return $amount * self::subUnitsFor($currency);
    }

    /**
     *
     * @param string $currency
     * @param numeric $amount
     * @return string
     */
    public function subunitsToAmount($currency, $subunits)
    {
        return $subunits / self::subUnitsFor($currency);
    }


    /**
     * Check whether the current currency is supported by the Omise API.
     * (This could/should probably come from Capbilities API in the future)
     *
     * Now, Omise API has no interface to check the supported currencies.
     * So, the supported currencies have been fixed in this function.
     *
     * @param string $currency
     * @return bool
     */
    public function isCurrencySupported($currency)
    {
        return in_array(strtoupper($currency), self::$supportedCurrencies);
    }


    public function formatPrice($currency, $subunitAmount)
    {
        return Mage::app()->getLocale()->currency(strtoupper($currency))->toCurrency($this->subunitsToAmount($currency, $subunitAmount));
    }

    public function internetBankingName($code)
    {
        $banks = Mage::getSingleton('omise_gateway/config')->getInternetBankingBanks();
        return $this->__(array_key_exists($code, $banks) ? $banks[$code] : "New bank ($code)");
    }

    public function installmentProviderName($code)
    {
        $providers = Mage::getSingleton('omise_gateway/config')->getInstallmentProviders();
        return $this->__(array_key_exists($code, $providers) ? $providers[$code]['name'] : "New provider ($code)");
    }

    public function installmentProviderInterestRate($code)
    {
        $providers = Mage::getSingleton('omise_gateway/config')->getInstallmentProviders();
        return (double)($providers[$code]['interest_rate']);
    }

    public function installmentProviderMonthlyMinimum($code, $currencyCode='thb')
    {
        $providers = Mage::getSingleton('omise_gateway/config')->getInstallmentProviders();
        return (int)($providers[$code]['monthly_minimum_subunits_'.$currencyCode]);
    }

    public function getTestModeJS()
    {
        $isTestMode = Mage::getModel('omise_gateway/config')->load(1)->test_mode;
        return $isTestMode ? 'omise/gateway/js/testmode.js' : '';
    }

    /**
     * Returns last checked out order in session.
     * @return Mage_Sales_Model_Order
     */
    public function getLastCheckedoutOrder()
    {
        $orderId = Mage::getModel('checkout/session')->getLastRealOrderId();
        return Mage::getModel('sales/order')->loadByIncrementId($orderId);
    }

    /**
     * Convert a given SVG Bill Payment Tesco's barcode to HTML format.
     *
     * Note that the SVG barcode contains with the following structure:
     *
     * <?xml version="1.0" encoding="UTF-8"?>
     * <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="515px" height="90px" viewBox="0 0 515 90" version="1.1" preserveAspectRatio="none">
     *   <title>** reference number **</title>
     *   <g id="canvas">
     *     <rect x="0" y="0" width="515px" height="90px" fill="#fff" />
     *     <g id="barcode" fill="#000">
     *       <rect x="20" y="20" width="2px" height="50px" />
     *       ... (repeat <rect> node for displaying barcode) ...
     *       <rect x="493" y="20" width="2px" height="50px" />
     *     </g>
     *   </g>
     * </svg>
     *
     * The following code in this method is to read all <rect> nodes' attributes under the <g id="barcode"></g>
     * in order to replicate the barcode in HTML <div></div> element.
     *
     * @param  string $barcode_svg
     *
     * @return string  of a generated Bill Payment Tesco's barcode in HTML format.
     */
    public function tescoBarcodeSvgToHtml( $charge )
    {
        $source = $charge->source;
        $barcode_url = $source['references']['barcode'];
        $barcode_svg = file_get_contents($barcode_url);
        $xml       = new SimpleXMLElement( $barcode_svg );
        $xhtml     = new DOMDocument();
        $prevX     = 0;
        $prevWidth = 0;

        $div_wrapper = $xhtml->createElement( 'div' );
        $div_wrapper->setAttribute( 'class', 'omise-barcode' );

        // Read data from all <rect> nodes.
        foreach ( $xml->g->g->children() as $rect ) {
            $attributes = $rect->attributes();
            $width      = $attributes['width'];
            $margin     = ( $attributes['x'] - $prevX - $prevWidth ) . 'px';

            // Set HTML attributes based on <rect> node's attributes.
            $div_rect = $xhtml->createElement( 'div' );
            $div_rect->setAttribute( 'style', "float: left; position: relative; height: 50px; border-left: $width solid #000000; width: 0; margin-left: $margin" );
            $div_wrapper->appendChild( $div_rect );

            $prevX     = $attributes['x'];
            $prevWidth = $attributes['width'];
        }

        $xhtml->appendChild( $div_wrapper );

        // Add an empty <div></div> element to clear those floating elements.
        $div = $xhtml->createElement( 'div' );
        $div->setAttribute( 'style', 'clear:both' );
        $xhtml->appendChild( $div );

        return $xhtml->saveXML( null, LIBXML_NOEMPTYTAG );
    }
    /**
     * returns reference code for barcode.
     * @param Omise_Gateway_Model_Api_Charge $charge
     * @return string
     */
    public function generateTescoReference($charge)
    {
        $source = $charge->source;
        return sprintf(
            '| &nbsp; %1$s &nbsp; 00 &nbsp; %2$s &nbsp; %3$s &nbsp; %4$s',
            $source['references']['omise_tax_id'],
            $source['references']['reference_number_1'],
            $source['references']['reference_number_2'],
            $charge->amount
        );
    }
}
