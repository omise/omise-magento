<?php

namespace Omise\Payment\Test\Unit\Gateway\Request\APMBuilders;

use Omise\Payment\Helper\OmiseMoney;
use Omise\Payment\Model\Config\Atome;
use Omise\Payment\Gateway\Request\APMBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Omise\Payment\Test\Unit\Gateway\Request\APMBuilders\APMBuilderTest;

class AtomeAPMBuilderTest extends APMBuilderTest
{
    protected function setUp(): void
    {
        parent::setup();

        $this->itemMock = $this->getMockBuilder(OrderItemInterface::class)->getMock();
        $this->addressMock = $this->getMockBuilder(AddressAdapterInterface::class)->getMock();
        $this->orderMock->method('getShippingAddress')->willReturn($this->addressMock);
        $this->orderMock->method('getItems')->willReturn([$this->itemMock]);
        $this->orderMock->method('getCurrencyCode')->willReturn('THB');
    }

    /**
     * @covers Omise\Payment\Gateway\Request\APMBuilder
     * @covers Omise\Payment\Model\Config\Atome
     */
    public function testApmBuilderWithItemPriceZero()
    {
        $this->itemMock->method('getPrice')->willReturn(0.0);
        $this->infoMock->method('getMethod')->willReturn(Atome::CODE);
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

        $this->assertEquals(0, count($result['source']['items']));
        $this->assertEquals('atome', $result['source']['type']);
        $this->assertEquals('https://omise.co/complete', $result['return_uri']);
    }

    /**
     * @covers Omise\Payment\Gateway\Request\APMBuilder
     * @covers Omise\Payment\Model\Config\Atome
     * @covers Omise\Payment\Helper\OmiseMoney
     */
    public function testApmBuilderWithItemPriceGreaterThanZero()
    {
        $this->itemMock->method('getPrice')->willReturn(100.0);
        $this->infoMock->method('getMethod')->willReturn(Atome::CODE);
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

        $this->assertEquals(1, count($result['source']['items']));
        $this->assertEquals('atome', $result['source']['type']);
        $this->assertEquals('https://omise.co/complete', $result['return_uri']);
    }

    /**
     * @covers Omise\Payment\Gateway\Request\APMBuilder
     * @covers Omise\Payment\Model\Config\Atome
     * @covers Omise\Payment\Helper\OmiseMoney
     */
    public function testApmBuilderWithSubProductNonZeroButHasParentItem()
    {
        $this->itemMock->method('getPrice')->willReturn(100.0);
        $this->itemMock->method('getParentItem')->willReturn($this->itemMock);
        $this->infoMock->method('getMethod')->willReturn(Atome::CODE);
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

        $this->assertEquals(0, count($result['source']['items']));
        $this->assertEquals('atome', $result['source']['type']);
        $this->assertEquals('https://omise.co/complete', $result['return_uri']);
    }

    /**
     * @covers Omise\Payment\Model\Config\Atome
     */
    public function testConstants()
    {
        $this->assertEquals('omise_offsite_atome', Atome::CODE);
        $this->assertEquals('atome', Atome::ID);
    }
}
