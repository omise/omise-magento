<?php

namespace Omise\Payment\Test\Unit\Gateway\Request\APMBuilders;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Omise\Payment\Gateway\Request\APMBuilder;
use Omise\Payment\Helper\OmiseMoney;
use Omise\Payment\Model\Config\Alipay;
use Omise\Payment\Model\Config\Alipayplus;
use Omise\Payment\Model\Config\OcbcDigital;
use Omise\Payment\Model\Config\Grabpay;
use Omise\Payment\Model\Config\Boost;
use Omise\Payment\Model\Config\DuitnowQR;
use Omise\Payment\Model\Config\MaybankQR;
use Omise\Payment\Model\Config\Touchngo;
use Omise\Payment\Model\Config\Promptpay;
use Omise\Payment\Model\Config\Paynow;
use Omise\Payment\Model\Config\Tesco;
use Omise\Payment\Model\Config\Rabbitlinepay;
use Omise\Payment\Model\Config\WeChatPay;
use Omise\Payment\Test\Unit\Gateway\Request\APMBuilders\APMBuilderTest;

class AlipayAPMBuilderTest extends APMBuilderTest
{
    private function initialize($code)
    {
        $this->infoMock->method('getMethod')->willReturn($code);
        $this->returnUrlHelper->method('create')->willReturn([
            'url' => 'https://omise.co/complete',
            'token' => '1234'
        ]);

        $this->builder = new APMBuilder(
            $this->returnUrlHelper,
            $this->config,
            $this->capabilities,
            new OmiseMoney(),
            $this->requestHelper
        );

        return $this->builder->build(['payment' => new PaymentDataObject(
            $this->orderMock,
            $this->infoMock
        )]);
    }

    /**
     * @dataProvider apmRequiringOnlySourceTypeDataProvider
     * @covers Omise\Payment\Gateway\Request\APMBuilder
     * @covers Omise\Payment\Model\Config\Alipay
     * @test
     */
    public function apmRequiringOnlySourceTypeBuilder($code, $id)
    {
        $result = $this->initialize($code);

        $this->assertEquals($id, $result['source']['type']);
        $this->assertEquals('https://omise.co/complete', $result['return_uri']);
    }

    /**
     * @dataProvider apmRequiringPlatformTypDataProvider
     * @covers Omise\Payment\Gateway\Request\APMBuilder
     * @test
     */
    public function apmRequiringAdditionalPlatformType($code, $id)
    {
        $platformType = 'WEB';
        $this->requestHelper->method('getPlatformType')->willReturn($platformType);
        $result = $this->initialize($code);

        $this->assertEquals($id, $result['source']['type']);
        $this->assertEquals('https://omise.co/complete', $result['return_uri']);
        $this->assertEquals($platformType, $result['source']['platform_type']);
    }

    /**
     * @dataProvider apmRequiringIpDataProvider
     * @covers Omise\Payment\Gateway\Request\APMBuilder
     * @test
     */
    public function apmRequiringAdditionalIp($code, $id)
    {
        $ip = '127.0.0.1';
        $this->requestHelper->method('getClientIp')->willReturn($ip);
        $result = $this->initialize($code);

        $this->assertEquals($id, $result['source']['type']);
        $this->assertEquals('https://omise.co/complete', $result['return_uri']);
        $this->assertEquals($ip, $result['source']['ip']);
    }

    public function apmRequiringOnlySourceTypeDataProvider()
    {
        return [
            [Alipay::CODE, Alipay::ID],
            [Tesco::CODE, Tesco::ID],
            [Paynow::CODE, Paynow::ID],
            [Promptpay::CODE, Promptpay::ID],
            [Rabbitlinepay::CODE, Rabbitlinepay::ID],
            [Boost::CODE, Boost::ID],
            [DuitnowQR::CODE, DuitnowQR::ID],
            [MaybankQR::CODE, MaybankQR::ID],
        ];
    }

    public function apmRequiringPlatformTypDataProvider()
    {
        return [
            [Alipayplus::ALIPAY_CODE, Alipayplus::ALIPAY_ID],
            [Alipayplus::ALIPAYHK_CODE, Alipayplus::ALIPAYHK_ID],
            [Alipayplus::DANA_CODE, Alipayplus::DANA_ID],
            [Alipayplus::GCASH_CODE, Alipayplus::GCASH_ID],
            [Alipayplus::KAKAOPAY_CODE, Alipayplus::KAKAOPAY_ID],
            [Grabpay::CODE, Grabpay::ID],
            [OcbcDigital::CODE, OcbcDigital::ID],
            [Touchngo::CODE, Touchngo::ID],
        ];
    }

    public function apmRequiringIpDataProvider()
    {
        return [
            [WeChatPay::CODE, WeChatPay::ID],
        ];
    }
}
