<?php

declare(strict_types=1);

namespace Omise\Payment\Test\Unit\Model;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Model\Omise;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Omise\Payment\Model\Omise
 * @covers ::__construct
 */
class OmiseTest extends TestCase
{
    private $configMock;
    private $moduleListMock;
    private $productMetadataMock;
    private $omise;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->moduleListMock = $this->createMock(ModuleListInterface::class);
        $this->productMetadataMock = $this->createMock(ProductMetadataInterface::class);

        $this->configMock->method('getPublicKey')->willReturn('public_key_123');
        $this->configMock->method('getSecretKey')->willReturn('secret_key_123');

        $this->productMetadataMock->method('getVersion')->willReturn('2.4.8');

        $this->moduleListMock
            ->method('getOne')
            ->with(Config::MODULE_NAME)
            ->willReturn(['setup_version' => '3.9.0']);

        $this->omise = new Omise(
            $this->configMock,
            $this->moduleListMock,
            $this->productMetadataMock
        );
    }

    /**
     * @covers ::defineApiKeys
     */
    public function testDefineApiKeysWithExplicitValues(): void
    {
        if (!defined('OMISE_PUBLIC_KEY')) {
            $this->omise->defineApiKeys('public_key_123', 'secret_key_123');
        }

        $this->assertSame('public_key_123', OMISE_PUBLIC_KEY);
        $this->assertSame('secret_key_123', OMISE_SECRET_KEY);
    }

    /**
     * @covers ::defineApiVersion
     */
    public function testDefineApiVersion(): void
    {
        if (!defined('OMISE_API_VERSION')) {
            $this->omise->defineApiVersion('2019-05-29');
        }

        $this->assertSame('2019-05-29', OMISE_API_VERSION);
    }

    /**
     * @covers ::defineUserAgent
     * @covers ::getMagentoVersion
     * @covers ::getModuleVersion
     */
    public function testDefineUserAgent(): void
    {
        if (!defined('OMISE_USER_AGENT_SUFFIX')) {
            $this->omise->defineUserAgent();
        }

        $this->assertSame(
            'OmiseMagento/3.9.0 Magento/2.4.8',
            OMISE_USER_AGENT_SUFFIX
        );
    }

    /**
     * @covers ::defineApiKeys
     */
    public function testDefineApiKeysUsesConfigValuesWhenEmpty(): void
    {
        if (!defined('OMISE_PUBLIC_KEY') && !defined('OMISE_SECRET_KEY')) {
            $this->omise->defineApiKeys();
        }

        $this->assertTrue(defined('OMISE_PUBLIC_KEY'));
        $this->assertTrue(defined('OMISE_SECRET_KEY'));
    }
}
