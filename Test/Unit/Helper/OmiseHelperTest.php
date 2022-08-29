<?php

namespace Omise\Payment\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Omise\Payment\Helper\OmiseHelper;
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
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Model\Config\CcGooglePay;
use Omise\Payment\Model\Config\Conveniencestore;

class OmiseHelperTest extends \PHPUnit\Framework\TestCase
{
    protected $omiseHelperMock;

    protected $configMock;

    protected $model;

    /**
     * This function is called before the test runs.
     * Ideal for setting the values to variables or objects.
     * @coversNothing
     */
    public function setUp(): void
    {
        $this->omiseHelperMock = $mockStaging = $this->createMock('Magento\Framework\HTTP\Header');
        $this->configMock = $mockStaging = $this->createMock('Omise\Payment\Model\Config\Config');
        $this->model = new OmiseHelper($this->omiseHelperMock, $this->configMock);
    }

    /**
     * This function is called after the test runs.
     * Ideal for setting the values to variables or objects.
     * @coversNothing
     */
    public function tearDown(): void
    {
    }

    /**
     * Test the function returns amount in correct format
     *
     * @dataProvider currencyProvider
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function omiseAmountFormatReturnCorrectFormat($currency)
    {
        $amount = 20;
        $expectedAmount = 2000;
        $formattedAmount = $this->model->omiseAmountFormat($currency, $amount);

        $this->assertEquals($expectedAmount, $formattedAmount);
    }

    /**
     * Data provider for the function omiseAmountFormatReturnCorrectFormat()
     */
    public function currencyProvider()
    {
        return [
            ['EUR'],
            ['GBP'],
            ['SGD'],
            ['THB'],
            ['USD'],
            ['AUD'],
            ['CAD'],
            ['CHF'],
            ['CNY'],
            ['DKK'],
            ['HKD'],
            ['MYR']
        ];
    }

    /**
     * Test the function isPayableByImageCode() returns true when correct code is passed
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function isPayableByImageCodeReturnsTrueWhenCorrectPaymentCodeIsPassed()
    {
        $isPayableByImageCode = $this->model->isPayableByImageCode(Paynow::CODE);
        $this->assertTrue($isPayableByImageCode);
    }

    /**
     * Test the function isPayableByImageCode() returns false when invalid code is passed
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function isPayableByImageCodeReturnsFalseWhenWrongPaymentCodeIsPassed()
    {
        $isPayableByImageCode = $this->model->isPayableByImageCode(CcGooglePay::CODE);
        $this->assertFalse($isPayableByImageCode);
    }

    /**
     * Test the function isOfflinePaymentMethod() returns true when correct code is passed
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function isOfflinePaymentMethodReturnsTrueWhenWrongPaymentCodeIsPassed()
    {
        $isOfflinePaymentMethod = $this->model->isOfflinePaymentMethod(Conveniencestore::CODE);
        $this->assertTrue($isOfflinePaymentMethod);
    }

    /**
     * Test the function isOfflinePaymentMethod() returns false when invalid code is passed
     * @covers \Omise\Payment\Helper\OmiseHelper
     */
    public function testIsOfflinePaymentMethodReturnsFalseWhenWrongPaymentCodeIsPassed()
    {
        $isOfflinePaymentMethod = $this->model->isOfflinePaymentMethod(CcGooglePay::CODE);
        $this->assertFalse($isOfflinePaymentMethod);
    }

    /**
     * Test the function isOffsitePaymentMethod() returns true when correct code is passed
     * @covers \Omise\Payment\Helper\OmiseHelper
     */
    public function isOffsitePaymentMethodReturnsTrueWhenWrongPaymentCodeIsPassed()
    {
        $isOffsitePaymentMethod = $this->model->isOffsitePaymentMethod(Truemoney::CODE);
        $this->assertTrue($isOffsitePaymentMethod);
    }

    /**
     * Test the function isOffsitePaymentMethod() returns false when invalid code is passed
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function isOffsitePaymentMethodReturnsFalseWhenWrongPaymentCodeIsPassed()
    {
        $isOffsitePaymentMethod = $this->model->isOffsitePaymentMethod(CcGooglePay::CODE);
        $this->assertFalse($isOffsitePaymentMethod);
    }

    /**
     * Test the function isOmisePayment() return true whe
     * correct payment code is passed
     *
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function isOmisePaymentReturnsTrueWhenCorrectPaymentCodeIsPassed()
    {
        $isOmisePayment = $this->model->isOmisePayment(CcGooglePay::CODE);
        $this->assertTrue($isOmisePayment);
    }

    /**
     * Test the function whether isCreditCardPaymentMethod() returns false
     * when invalid code is passed
     *
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function isCreditCardPaymentMethodReturnFalseIfWrongPaymentCodeIsPassed()
    {
        $isCreditCardPaymentMethod = $this->model->isCreditCardPaymentMethod(Paynow::CODE);
        $this->assertFalse($isCreditCardPaymentMethod);
    }

    /**
     * Test the function getPlatformType() return correct platform as per user agent
     *
     * @dataProvider platformTypeProvider
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function getPlatformTypeReturnsCorrectPlatform($platform, $expectedValue)
    {
        $omiseHelperMock = $this->omiseHelperMock;
        $omiseHelperMock->method('getHttpUserAgent')
            ->willReturn($platform);

        $result = $this->model->getPlatformType();
        
        $this->assertEquals($expectedValue, $result);
    }

    public function platformTypeProvider()
    {
        return [
            ['Android', 'ANDROID'],
            ['android', 'ANDROID'],
            ['ipad', 'IOS'],
            ['IPAD', 'IOS'],
            ['iPad', 'IOS'],
            ['iphone', 'IOS'],
            ['IPHONE', 'IOS'],
            ['iPhone', 'IOS'],
            ['ipod', 'IOS'],
            ['IPOD', 'IOS'],
            ['iPod', 'IOS'],
            ['Mozilla', 'WEB'],
        ];
    }

    /**
     * Test the function is3DSecureEnabled() whether 3DS is enabled or not
     * by checking charge object
     *
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function is3DSecureEnabledReturnsTrue()
    {
        $charge = (object)[
            'status' => 'pending',
            'authorized' => false,
            'paid' => false,
            'authorize_uri' => 'https://somefakeuri.com/redirect'
        ];

        $result = $this->model->is3DSecureEnabled($charge);

        $this->assertTrue($result);
    }

    /**
     * Test the function is3DSecureEnabled() returns false if the value of
     * any one properties of charge does not match the condition
     *
     * @dataProvider chargeProvider
     * @covers \Omise\Payment\Helper\OmiseHelper
     * @test
     */
    public function is3DSecureEnabledReturnsFalse($charge)
    {
        $result = $this->model->is3DSecureEnabled($charge);
        $this->assertFalse($result);
    }

    public function chargeProvider()
    {
        return [
            [(object)[
                'status' => 'canceled',
                'authorized' => false,
                'paid' => false,
                'authorize_uri' => 'https://somefakeuri.com/redirect'
            ]],
            [(object)[
                'status' => 'pending',
                'authorized' => true,
                'paid' => false,
                'authorize_uri' => 'https://somefakeuri.com/redirect'
            ]],
            [(object)[
                'status' => 'pending',
                'authorized' => false,
                'paid' => true,
                'authorize_uri' => 'https://somefakeuri.com/redirect'
            ]],
            [(object)[
                'status' => 'pending',
                'authorized' => false,
                'paid' => false,
                'authorize_uri' => ''
            ]]
        ];
    }
}
