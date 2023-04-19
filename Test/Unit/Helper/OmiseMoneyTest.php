<?php

namespace Omise\Payment\Test\Unit\Helper;

use Omise\Payment\Helper\OmiseMoney;

class OmiseMoneyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OmiseMoney;
     */
    private $model;
    /**
     * This function is called before the test runs.
     * Ideal for setting the values to variables or objects.
     * @coversNothing
     */
    public function setUp(): void
    {
        $this->model = new OmiseMoney();
    }

    /**
     * Test the function returns amount in correct format
     *
     * @dataProvider toSubunitProvider
     * @covers \Omise\Payment\Helper\OmiseMoney
     * @test
     */
    public function toSubunitReturnCorrectFormat($currency, $amount, $expected)
    {
        $this->assertEquals($expected, $this->model->parse($amount, $currency)->toSubunit());
    }

    /**
     * Test the function returns amount in correct format
     *
     * @dataProvider toCurrencyUnitProvider
     * @covers \Omise\Payment\Helper\OmiseMoney
     * @test
     */
    public function toCurrencyUnitReturnCorrectFormat($currency, $amount, $expected)
    {
        $this->assertEquals($expected, $this->model->parse($amount, $currency)->toCurrencyUnit());
    }

    /**
     * Data provider for toSubunitReturnCorrectFormat
     */
    public function toSubunitProvider()
    {
        return [
            ['CNY', 20.996, 2100],
            ['DKK', 20.556, 2056],
            ['HKD', 20.126, 2013],
            ['MYR', 20.005, 2001],
            ['EUR', 21.996, 2200],
            ['GBP', 20.000, 2000],
            ['SGD', 20.00, 2000],
            ['THB', 20.12, 2012],
            ['USD', 20.99, 2099],
            ['AUD', 20.88, 2088],
            ['CAD', 20.45, 2045],
            ['CHF', 20.123, 2012],
            ['JPY', 20, 20],
        ];
    }

    /**
     * Data provider for toCurrencyUnitReturnCorrectFormat
     */
    public function toCurrencyUnitProvider()
    {
        return [
            ['CNY', 2100, 21],
            ['DKK', 2056, 20.56],
            ['HKD', 2013, 20.13],
            ['MYR', 2001, 20.01],
            ['EUR', 2200, 22],
            ['GBP', 2000, 20],
            ['SGD', 2000, 20],
            ['THB', 2012, 20.12],
            ['USD', 2099, 20.99],
            ['AUD', 2088, 20.88],
            ['CAD', 2045, 20.45],
            ['CHF', 2012, 20.12],
            ['JPY', 20, 20],
        ];
    }
}
