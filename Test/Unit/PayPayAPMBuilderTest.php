<?php

namespace Omise\Payment\Test\Unit;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Omise\Payment\Gateway\Request\APMBuilder;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Helper\OmiseMoney;
use Omise\Payment\Helper\ReturnUrlHelper;
use Omise\Payment\Model\Capabilities;
use Omise\Payment\Model\Config\Config;
use PHPUnit\Framework\TestCase;
use Omise\Payment\Model\Config\PayPay;
use Omise\Payment\Test\Mock\InfoMock;

class PayPayAPMBuilderTest extends TestCase
{
    private $builder;
    private $helper;
    private $returnUrlHelper;
    private $config;
    private $capabilities;
    private $orderMock;
    private $infoMock;

    protected function setUp(): void
    {
        $this->helper = $this->getMockBuilder(OmiseHelper::class)->disableOriginalConstructor()->getMock();
        $this->returnUrlHelper = $this->getMockBuilder(ReturnUrlHelper::class)->disableOriginalConstructor()->getMock();
        $this->config = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $this->capabilities = $this->getMockBuilder(Capabilities::class)->disableOriginalConstructor()->getMock();
        $this->orderMock = $this->getMockBuilder(OrderAdapterInterface::class)->getMock();
        $this->infoMock = $this->getMockBuilder(InfoMock::class)->getMock();
    }

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