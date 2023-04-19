<?php

namespace Omise\Payment\Helper;

class OmiseMoney
{

    private $zeroDecimalCurrencies = ['JPY'];

    /**
     * @var integer|float
     */
    private $amount;

    /**
     * @var string
     */
    private $currency;

    /**
     * @param integer|float $amount
     * @param string $currency
     */
    public function __construct($amount, $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * @param integer|float $amount
     * @param string $currency
     */
    public static function parse($amount, $currency)
    {
        return new self($amount, $currency);
    }

    /**
     * convert currency unit to subunit
     * @return integer
     */
    function toSubunit()
    {
        if (in_array($this->currency, $this->zeroDecimalCurrencies)) {
            return $this->amount;
        }
        return round($this->amount * 100, 0);
    }

    /**
     * convert subunit to currency unit
     * @return float|integer
     */
    function toCurrencyUnit()
    {
        if (in_array($this->currency, $this->zeroDecimalCurrencies)) {
            return $this->amount;
        }
        return round($this->amount / 100, 2);
    }
}
