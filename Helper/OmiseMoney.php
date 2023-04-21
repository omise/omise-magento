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
     * @param this
     */
    public function parse($amount, $currency)
    {
        $this->amount = $amount;
        $this->currency = strtoupper($currency);
        return $this;
    }

    /**
     * convert currency unit to subunit
     * @return integer
     */
    public function toSubunit()
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
    public function toUnit()
    {
        if (in_array($this->currency, $this->zeroDecimalCurrencies)) {
            return $this->amount;
        }
        return round($this->amount / 100, 2);
    }
}
