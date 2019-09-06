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

}
