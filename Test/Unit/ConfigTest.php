<?php

namespace Omise\Payment\Test\Unit;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Omise\Payment\Model\Config\Config;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigTest extends TestCase
{
    private $storeManagerMock;
    private $scopeConfigMock;
    private $storeMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = m::mock(ScopeConfigInterface::class);
        $this->storeManagerMock = m::mock(StoreManagerInterface::class);
        $this->storeMock =  m::mock(StoreInterface::class);
    }

    /**
     * @dataProvider isDynamicWebhooksEnabledProvider
     * @covers Omise\Payment\Model\Config\Config
     */
    public function testIsDynamicWebhooksEnabled($webhookEnabled, $expected)
    {
        $this->scopeConfigMock->shouldReceive('getValue')->andReturn($webhookEnabled);
        $this->storeMock->shouldReceive('getId')->andReturn(1);
        $this->storeManagerMock->shouldReceive('getStore')->andReturn($this->storeMock);

        $config = new Config($this->scopeConfigMock, $this->storeManagerMock);
        $result = $config->isDynamicWebhooksEnabled();
        $this->assertEquals($result, $expected);
    }

    /**
     * Data provider for testIsDynamicWebhooksEnabled method
     */
    public function isDynamicWebhooksEnabledProvider()
    {
        return [
            [
                'webhookEnabled' => 1,
                'expected' => true,
            ],
            [
                'webhookEnabled' => 0,
                'expected' => false,
            ],
        ];
    }
}
