<?php

namespace Omise\Payment\Test\Unit\Gateway\Request\APMBuilders;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Omise\Payment\Gateway\Request\APMBuilder;
use Omise\Payment\Helper\OmiseMoney;
use Omise\Payment\Model\Config\PayPay;
use Omise\Payment\Test\Unit\Gateway\Request\APMBuilders\APMBuilderTest;

class PayPayAPMBuilderTest extends APMBuilderTest
{
    /**
     * @covers Omise\Payment\Gateway\Request\APMBuilder
     * @covers Omise\Payment\Model\Config\PayPay
     */
    public function testApmBuilder()
    {
        $this->infoMock->method('getMethod')->willReturn(PayPay::CODE);
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

        $result = $this->builder->build(['payment' => new PaymentDataObject(
            $this->orderMock,
            $this->infoMock
        )]);

        $this->assertEquals('paypay', $result['source']['type']);
        $this->assertEquals('https://omise.co/complete', $result['return_uri']);
    }

    /**
     * @covers Omise\Payment\Model\Config\PayPay
     */
    public function testConstants()
    {
        $this->assertEquals('omise_offsite_paypay', PayPay::CODE);
        $this->assertEquals('paypay', PayPay::ID);
    }
}
