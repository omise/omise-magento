<?php

namespace Omise\Payment\Test\Unit\Gateway\Request\APMBuilders;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Omise\Payment\Helper\RequestHelper;
use Omise\Payment\Helper\ReturnUrlHelper;
use Omise\Payment\Model\Capabilities;
use Omise\Payment\Model\Config\Config;
use PHPUnit\Framework\TestCase;
use Omise\Payment\Test\Mock\InfoMock;

abstract class APMBuilderTest extends TestCase
{
    protected $builder;
    protected $requestHelper;
    protected $returnUrlHelper;
    protected $config;
    protected $capabilities;
    protected $orderMock;
    protected $infoMock;

    protected function setUp(): void
    {
        $this->requestHelper = $this->getMockBuilder(RequestHelper::class)->disableOriginalConstructor()->getMock();
        $this->returnUrlHelper = $this->getMockBuilder(ReturnUrlHelper::class)->disableOriginalConstructor()->getMock();
        $this->config = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $this->capabilities = $this->getMockBuilder(Capabilities::class)->disableOriginalConstructor()->getMock();
        $this->orderMock = $this->getMockBuilder(OrderAdapterInterface::class)->getMock();
        $this->infoMock = $this->getMockBuilder(InfoMock::class)->getMock();
    }
}
