<?php

namespace Omise\Payment\Test\Unit\Gateway\Request\APMBuilders;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Omise\Payment\Gateway\Request\APMBuilder;
use Omise\Payment\Helper\OmiseMoney;
use Omise\Payment\Model\Config\Installment;
use Omise\Payment\Test\Unit\Gateway\Request\APMBuilders\APMBuilderTest;

class InstallmentAPMBuilderTest extends APMBuilderTest
{
    /**
     * @covers Omise\Payment\Gateway\Request\APMBuilder
     */
    public function testApmBuilderForInstallment()
    {
        $this->infoMock->method('getMethod')->willReturn(Installment::CODE);
        $this->returnUrlHelper->method('create')->willReturn([
            'url' => 'https://omise.co/complete',
            'card' => 'mock_card',
            'source' => 'mock_source',
            'token' => 'mock_token'
        ]);
        $this->infoMock->method('getAdditionalInformation')->willReturn('mock_source');

        $this->builder = new APMBuilder(
            $this->returnUrlHelper,
            $this->config,
            $this->capability,
            new OmiseMoney(),
            $this->omiseHelper,
            $this->requestHelper,
            $this->deploymentConfig
        );

        $result = $this->builder->build(['payment' => new PaymentDataObject(
            $this->orderMock,
            $this->infoMock
        )]);

        $this->assertEquals('mock_source', $result['source']);
        $this->assertEquals('https://omise.co/complete', $result['return_uri']);
    }

    /**
     * @covers Omise\Payment\Gateway\Request\APMBuilder
     */
    public function testApmBuilderUsesCustomWlbDescription()
    {
        $this->infoMock->method('getMethod')->willReturn(Installment::CODE);
        $this->infoMock->method('getAdditionalInformation')->willReturn('mock_source');
        $this->returnUrlHelper->method('create')->willReturn([
            'url' => 'https://omise.co/complete',
            'token' => 'mock_token'
        ]);
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with(APMBuilder::OMISE_CUSTOM_WLB_DESCRIPTION)
            ->willReturn('Payment for {original_description}');
        $this->omiseHelper->expects($this->once())
            ->method('getDescription')
            ->with($this->orderMock)
            ->willReturn('Order 123');

        $this->builder = new APMBuilder(
            $this->returnUrlHelper,
            $this->config,
            $this->capability,
            new OmiseMoney(),
            $this->omiseHelper,
            $this->requestHelper,
            $this->deploymentConfig
        );

        $result = $this->builder->build(['payment' => new PaymentDataObject(
            $this->orderMock,
            $this->infoMock
        )]);

        $this->assertSame('Payment for Order 123', $result[APMBuilder::DESCRIPTION]);
    }
}
