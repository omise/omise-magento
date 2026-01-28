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
     * @covers \Omise\Payment\Model\Config\Config::__construct
     * @covers Omise\Payment\Model\Config\Config::canInitialize
     * @covers \Omise\Payment\Model\Config\Config::getPublicKey
     * @covers \Omise\Payment\Model\Config\Config::getSecretKey
     * @covers \Omise\Payment\Model\Config\Config::getTestPublicKey
     * @covers \Omise\Payment\Model\Config\Config::getTestSecretKey
     * @covers \Omise\Payment\Model\Config\Config::getValue
     * @covers \Omise\Payment\Model\Config\Config::init
     * @covers \Omise\Payment\Model\Config\Config::isSandboxEnabled
     * @covers \Omise\Payment\Model\Config\Config::setStoreId
     * @covers \Omise\Payment\Model\Config\Config::setStoreLocale
     */
    public function testCanInitialize()
    {
        // Mock all constructor dependencies
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
            ->andReturn('skey_test_xx');

        $this->scopeConfigMock->shouldReceive('getValue')
            ->with('payment/omise/dynamic_webhooks', m::any(), m::any())
            ->andReturn(0);

        $this->scopeConfigMock->shouldReceive('getValue')
            ->with('payment/omise/webhook_status', m::any(), m::any())
            ->andReturn(1);

        $this->storeMock->shouldReceive('getId')->andReturn(1);
        $this->storeManagerMock->shouldReceive('getStore')->andReturn($this->storeMock);

        // Create Config object
        $config = new Config($this->scopeConfigMock, $this->storeManagerMock);

        // Use Reflection to set protected property canInitialize
        $reflection = new \ReflectionClass($config);
        $property = $reflection->getProperty('canInitialize');
        $property->setAccessible(true);

        // Test true case
        $property->setValue($config, true);
        $this->assertTrue($config->canInitialize());

        // Test false case
        $property->setValue($config, false);
        $this->assertFalse($config->canInitialize());
    }

    /**
     * @covers \Omise\Payment\Model\Config\Config::getStoreLocale
     * @covers \Omise\Payment\Model\Config\Config::__construct
     * @covers \Omise\Payment\Model\Config\Config::getPublicKey
     * @covers \Omise\Payment\Model\Config\Config::getSecretKey
     * @covers \Omise\Payment\Model\Config\Config::getTestPublicKey
     * @covers \Omise\Payment\Model\Config\Config::getTestSecretKey
     * @covers \Omise\Payment\Model\Config\Config::getValue
     * @covers \Omise\Payment\Model\Config\Config::init
     * @covers \Omise\Payment\Model\Config\Config::isSandboxEnabled
     * @covers \Omise\Payment\Model\Config\Config::setStoreId
     * @covers \Omise\Payment\Model\Config\Config::setStoreLocale
     */

    public function testGetStoreLocale()
    {
        // Mock ScopeConfigInterface
        $scopeConfig = \Mockery::mock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $scopeConfig->shouldReceive('getValue')->andReturn('en_US');

        // Mock Store
        $storeMock = \Mockery::mock(\Magento\Store\Api\Data\StoreInterface::class);
        $storeMock->shouldReceive('getId')->andReturn(1);

        $storeManager = \Mockery::mock(\Magento\Store\Model\StoreManagerInterface::class);
        $storeManager->shouldReceive('getStore')->andReturn($storeMock);

        // Instantiate real Config object
        $config = new \Omise\Payment\Model\Config\Config($scopeConfig, $storeManager);

        // Now get the store locale
        $this->assertEquals('en_US', $config->getStoreLocale());
    }

    /**
     * @covers \Omise\Payment\Model\Config\Config::getLivePublicKey
     * @covers \Omise\Payment\Model\Config\Config::getValue
     * @covers \Omise\Payment\Model\Config\Config::__construct
     * @covers \Omise\Payment\Model\Config\Config::getPublicKey
     * @covers \Omise\Payment\Model\Config\Config::getSecretKey
     * @covers \Omise\Payment\Model\Config\Config::getTestPublicKey
     * @covers \Omise\Payment\Model\Config\Config::getTestSecretKey
     * @covers \Omise\Payment\Model\Config\Config::getValue
     * @covers \Omise\Payment\Model\Config\Config::init
     * @covers \Omise\Payment\Model\Config\Config::isSandboxEnabled
     * @covers \Omise\Payment\Model\Config\Config::setStoreId
     * @covers \Omise\Payment\Model\Config\Config::setStoreLocale
     */
    public function testGetLivePublicKey()
    {
        // Mock ScopeConfigInterface
        $scopeConfig = \Mockery::mock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        // The constructor calls getValue for several config paths
        $scopeConfig->shouldReceive('getValue')
            ->with('general/locale/code', m::any(), m::any())
            ->andReturn('en');

        $scopeConfig->shouldReceive('getValue')
            ->with('payment/omise/sandbox_status', m::any(), m::any())
            ->andReturn(1);

        $scopeConfig->shouldReceive('getValue')
            ->with('payment/omise/test_public_key', m::any(), m::any())
            ->andReturn('test_pkey_xxx');

        $scopeConfig->shouldReceive('getValue')
            ->with('payment/omise/test_secret_key', m::any(), m::any())
            ->andReturn('test_skey_xxx');

        $scopeConfig->shouldReceive('getValue')
            ->with('payment/omise/dynamic_webhooks', m::any(), m::any())
            ->andReturn(0);

        $scopeConfig->shouldReceive('getValue')
            ->with('payment/omise/webhook_status', m::any(), m::any())
            ->andReturn(1);

        // Also mock getValue for the method we are testing
        $scopeConfig->shouldReceive('getValue')
            ->with('payment/omise/live_public_key', m::any(), m::any())
            ->andReturn('live_pkey_xxx');

        // Mock Store
        $storeMock = \Mockery::mock(\Magento\Store\Api\Data\StoreInterface::class);
        $storeMock->shouldReceive('getId')->andReturn(1);

        $storeManager = \Mockery::mock(\Magento\Store\Model\StoreManagerInterface::class);
        $storeManager->shouldReceive('getStore')->andReturn($storeMock);

        // Instantiate real Config object
        $config = new \Omise\Payment\Model\Config\Config($scopeConfig, $storeManager);

        // Use Reflection to access protected method
        $reflection = new \ReflectionClass($config);
        $method = $reflection->getMethod('getLivePublicKey');
        $method->setAccessible(true);

        // Assert
        $this->assertEquals('live_pkey_xxx', $method->invoke($config));
    }

    /**
     * @covers \Omise\Payment\Model\Config\Config::getLiveSecretKey
     * @covers \Omise\Payment\Model\Config\Config::getValue
     * @covers \Omise\Payment\Model\Config\Config::__construct
     * @covers \Omise\Payment\Model\Config\Config::getPublicKey
     * @covers \Omise\Payment\Model\Config\Config::getSecretKey
     * @covers \Omise\Payment\Model\Config\Config::getTestPublicKey
     * @covers \Omise\Payment\Model\Config\Config::getTestSecretKey
     * @covers \Omise\Payment\Model\Config\Config::getValue
     * @covers \Omise\Payment\Model\Config\Config::init
     * @covers \Omise\Payment\Model\Config\Config::isSandboxEnabled
     * @covers \Omise\Payment\Model\Config\Config::setStoreId
     * @covers \Omise\Payment\Model\Config\Config::setStoreLocale
     */
    public function testGetLiveSecretKey()
    {
        // Mock ScopeConfigInterface
        $scopeConfig = \Mockery::mock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        // Mock getValue calls constructor will make
        $scopeConfig->shouldReceive('getValue')
            ->with('general/locale/code', m::any(), m::any())
            ->andReturn('en');

        $scopeConfig->shouldReceive('getValue')
            ->with('payment/omise/sandbox_status', m::any(), m::any())
            ->andReturn(1);

        $scopeConfig->shouldReceive('getValue')
            ->with('payment/omise/test_public_key', m::any(), m::any())
            ->andReturn('test_pkey_xxx');

        $scopeConfig->shouldReceive('getValue')
            ->with('payment/omise/test_secret_key', m::any(), m::any())
            ->andReturn('test_skey_xxx');

        $scopeConfig->shouldReceive('getValue')
            ->with('payment/omise/dynamic_webhooks', m::any(), m::any())
            ->andReturn(0);

        $scopeConfig->shouldReceive('getValue')
            ->with('payment/omise/webhook_status', m::any(), m::any())
            ->andReturn(1);

        // Mock getValue for the method we are testing
        $scopeConfig->shouldReceive('getValue')
            ->with('payment/omise/live_secret_key', m::any(), m::any())
            ->andReturn('live_skey_xxx');

        // Mock Store
        $storeMock = \Mockery::mock(\Magento\Store\Api\Data\StoreInterface::class);
        $storeMock->shouldReceive('getId')->andReturn(1);

        $storeManager = \Mockery::mock(\Magento\Store\Model\StoreManagerInterface::class);
        $storeManager->shouldReceive('getStore')->andReturn($storeMock);

        // Instantiate real Config object
        $config = new \Omise\Payment\Model\Config\Config($scopeConfig, $storeManager);

        // Use Reflection to access protected method
        $reflection = new \ReflectionClass($config);
        $method = $reflection->getMethod('getLiveSecretKey');
        $method->setAccessible(true);

        // Assert it returns the mocked live secret key
        $this->assertEquals('live_skey_xxx', $method->invoke($config));
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
