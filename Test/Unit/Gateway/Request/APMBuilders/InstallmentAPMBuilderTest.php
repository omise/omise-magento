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
            $this->capabilities,
            new OmiseMoney(),
            $this->requestHelper
        );

        $result = $this->builder->build(['payment' => new PaymentDataObject(
            $this->orderMock,
            $this->infoMock
        )]);

        $this->assertEquals('mock_source', $result['source']);
        $this->assertEquals('https://omise.co/complete', $result['return_uri']);
    }
}
