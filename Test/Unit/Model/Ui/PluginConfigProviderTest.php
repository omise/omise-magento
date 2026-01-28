<?php

namespace Omise\Payment\Test\Unit\Model\Ui;

use Omise\Payment\Model\Ui\PluginConfigProvider;
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Model\Config\CcGooglePay;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Omise\Payment\Model\Ui\PluginConfigProvider
 */
class PluginConfigProviderTest extends TestCase
{
    /** @var Config|\PHPUnit\Framework\MockObject\MockObject */
    private $configMock;

    /** @var PluginConfigProvider */
    private $provider;

    /**
     * @covers \Omise\Payment\Model\Ui\PluginConfigProvider::__construct
     * @covers \Omise\Payment\Model\Ui\PluginConfigProvider::getConfig
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->provider = new PluginConfigProvider($this->configMock);
    }

    /**
     * @covers ::__construct
     * @covers ::getConfig
     */
    public function testGetConfigReturnsCorrectStructure()
    {
        // Setup mock expectations
        $this->configMock->expects($this->once())
            ->method('isSandboxEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->exactly(3))
            ->method('getValue')
            ->withConsecutive(
                ['merchant_id', CcGooglePay::CODE],
                ['request_billing_address', CcGooglePay::CODE],
                ['request_phone_number', CcGooglePay::CODE]
            )
            ->willReturnOnConsecutiveCalls('merchant_123', true, false);

        $expected = [
            'isOmiseSandboxOn' => true,
            CcGooglePay::CODE => [
                'merchantId' => 'merchant_123',
                'requestBillingAddress' => true,
                'requestPhoneNumber' => false
            ]
        ];

        $this->assertEquals($expected, $this->provider->getConfig());
    }
}
