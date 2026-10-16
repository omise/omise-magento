<?php

namespace Omise\Payment\Test\Unit;

use PHPUnit\Framework\TestCase;
use Omise\Payment\Model\Config\Config;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigTest extends TestCase
{
    private $storeManagerMock;
    private $scopeConfigMock;
    private $storeMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeMock = $this->createMock(StoreInterface::class);
    }

    /**
     * @covers \Omise\Payment\Model\Config\Config::checkoutSessionEndpoint
     */
    public function testCheckoutSessionEndpoint(): void
    {
        $config = (new \ReflectionClass(Config::class))->newInstanceWithoutConstructor();

        $this->assertSame(
            'https://checkout-page.omise.co/',
            $config->checkoutSessionEndpoint()
        );
    }

    /**
     * @dataProvider isDynamicWebhooksEnabledProvider
     * @covers Omise\Payment\Model\Config\Config
     */
    public function testIsDynamicWebhooksEnabled($webhookEnabled, $dynamicWebhooksEnabled, $expected)
    {
        $this->scopeConfigMock->method('getValue')->willReturnMap([
            ['general/locale/code', ScopeInterface::SCOPE_STORE, 1, 'en'],
            ['payment/omise/sandbox_status', ScopeInterface::SCOPE_STORE, 1, 1],
            ['payment/omise/test_public_key', ScopeInterface::SCOPE_STORE, 1, 'pkey_test_xx'],
            ['payment/omise/test_secret_key', ScopeInterface::SCOPE_STORE, 1, 'pkey_test_xx'],
            ['payment/omise/dynamic_webhooks', ScopeInterface::SCOPE_STORE, 1, $dynamicWebhooksEnabled],
            ['payment/omise/webhook_status', ScopeInterface::SCOPE_STORE, 1, $webhookEnabled],
        ]);

        $this->storeMock->method('getId')->willReturn(1);
        $this->storeManagerMock->method('getStore')->willReturn($this->storeMock);

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
