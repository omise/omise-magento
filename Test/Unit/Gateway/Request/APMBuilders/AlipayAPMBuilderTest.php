<?php

namespace Omise\Payment\Test\Unit\Gateway\Request\APMBuilders;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Omise\Payment\Gateway\Request\APMBuilder;
use Omise\Payment\Helper\OmiseMoney;
use Omise\Payment\Model\Config\Alipay;
use Omise\Payment\Test\Unit\Gateway\Request\APMBuilders\APMBuilderTest;

class AlipayAPMBuilderTest extends APMBuilderTest
{
    /**
     * @covers Omise\Payment\Gateway\Request\APMBuilder
     * @covers Omise\Payment\Model\Config\Alipay
     */
    public function testApmBuilder()
    {
        $this->infoMock->method('getMethod')->willReturn(Alipay::CODE);
        $this->returnUrlHelper->method('create')->willReturn([
            'url' => 'https://omise.co/complete',
            'token' => '1234'
        ]);

        $this->builder = new APMBuilder(
            $this->helper,
            $this->returnUrlHelper,
            $this->config,
            $this->capabilities,
            new OmiseMoney(),
        );

        $result = $this->builder->build(['payment' => new PaymentDataObject(
            $this->orderMock,
            $this->infoMock
        )]);

        $this->assertEquals('alipay', $result['source']['type']);
        $this->assertEquals('https://omise.co/complete', $result['return_uri']);
    }

    /**
     * @covers Omise\Payment\Model\Config\Alipay
     */
    public function testConstants()
    {
        $this->assertEquals('omise_offsite_alipay', Alipay::CODE);
        $this->assertEquals('alipay', Alipay::ID);
    }
}
