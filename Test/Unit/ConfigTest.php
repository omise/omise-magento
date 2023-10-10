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
    public function testIsDynamicWebhooksEnabled($webhookEnabled, $dynamicWebhooksEnabled, $expected)
    {
        $this->scopeConfigMock->shouldReceive('getValue')
            ->with('general/locale/code', m::any(), m::any())
            ->andReturn('en');

        $this->scopeConfigMock->shouldReceive('getValue')
            ->with('payment/omise/sandbox_status', m::any(), m::any())
            ->andReturn(1);

        $this->scopeConfigMock->shouldReceive('getValue')
            ->with('payment/omise/test_public_key', m::any(), m::any())
            ->andReturn('pkey_test_xx');

        $this->scopeConfigMock->shouldReceive('getValue')
            ->with('payment/omise/test_secret_key', m::any(), m::any())
            ->andReturn('pkey_test_xx');


        $this->scopeConfigMock->shouldReceive('getValue')
            ->with('payment/omise/dynamic_webhooks', m::any(), m::any())
            ->andReturn($dynamicWebhooksEnabled);

        $this->scopeConfigMock->shouldReceive('getValue')
            ->with('payment/omise/webhook_status', m::any(), m::any())
            ->andReturn($webhookEnabled);

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
                'dynamicWebhooksEnabled' => 1,
                'expected' => true,
            ],
            [
                'webhookEnabled' => 1,
                'dynamicWebhooksEnabled' => 1,
                'expected' => true,
            ],
            [
                'webhookEnabled' => 0,
                'dynamicWebhooksEnabled' => 1,
                'expected' => false,
            ],
            [
                'webhookEnabled' => 0,
                'dynamicWebhooksEnabled' => 0,
                'expected' => false,
            ],
        ];
    }
}
