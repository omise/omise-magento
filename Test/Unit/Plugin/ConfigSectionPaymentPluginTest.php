<?php

namespace Omise\Payment\Test\Unit\Plugin;

use PHPUnit\Framework\TestCase;
use Omise\Payment\Plugin\ConfigSectionPaymentPlugin;
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Helper\OmiseHelper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Config\Model\Config as CoreConfig;

/**
 * @covers \Omise\Payment\Plugin\ConfigSectionPaymentPlugin
 */
class ConfigSectionPaymentPluginTest extends TestCase
{
    private ConfigSectionPaymentPlugin $plugin;
    private CoreConfig $coreConfig;
    private OmiseHelper $helper;
    private ManagerInterface $messageManager;
    private ScopeConfigInterface $scopeConfig;
    private Config $config;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->helper = $this->createMock(OmiseHelper::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);

        $this->coreConfig = $this->getMockBuilder(CoreConfig::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toArray', 'setData'])       // existing methods
            ->addMethods(['getSection', 'getGroups', 'getWebsite', 'getStore']) // magic methods
            ->getMock();

        $this->plugin = new ConfigSectionPaymentPlugin(
            $this->config,
            $this->helper,
            $this->messageManager,
            $this->scopeConfig
        );
    }

    /**
     * @covers \Omise\Payment\Plugin\ConfigSectionPaymentPlugin::beforeSave
     */
    public function testBeforeSaveIgnoredWhenNotPaymentSection(): void
    {
        $this->coreConfig->method('getSection')->willReturn('general');
        $this->plugin->beforeSave($this->coreConfig);
        $this->assertTrue(true);
    }

    /**
     * @covers \Omise\Payment\Plugin\ConfigSectionPaymentPlugin::beforeSave
     */
    public function testBeforeSaveWithEmptyKeys(): void
    {
        $this->coreConfig->method('getSection')->willReturn('payment');
        $this->coreConfig->method('toArray')->willReturn([
            'groups' => [
                'omise' => [
                    'fields' => [
                        'sandbox_status' => ['value' => 1],
                        'test_public_key' => [],
                        'test_secret_key' => [],
                    ]
                ]
            ]
        ]);

        $this->config->method('getPublicKey')->willReturn('');
        $this->config->method('getSecretKey')->willReturn('');

        $this->plugin->beforeSave($this->coreConfig);
        $this->assertTrue(true);
    }

    /**
     * @covers \Omise\Payment\Plugin\ConfigSectionPaymentPlugin::validatePaymentMethods
     */
    public function testValidateUnsupportedPayment(): void
    {
        $this->messageManager
            ->expects($this->once())
            ->method('addError');

        $this->coreConfig->method('getGroups')->willReturn([
            'omise' => [
                'groups' => [
                    'alipay' => [
                        'fields' => ['active' => ['value' => 1]]
                    ]
                ]
            ]
        ]);

        $method = new \ReflectionMethod($this->plugin, 'validatePaymentMethods');
        $method->setAccessible(true);

        $method->invoke(
            $this->plugin,
            ['promptpay'],
            ['alipay' => 'Alipay'],
            $this->coreConfig
        );
    }

    /**
     * @covers \Omise\Payment\Plugin\ConfigSectionPaymentPlugin::getPaymentMethods
     */
    public function testGetPaymentMethods(): void
    {
        // Setup capability property
        $capability = [
            'payment_methods' => [
                ['name' => 'promptpay'],
                ['name' => 'alipay']
            ],
            'tokenization_methods' => [
                'internet_banking'
            ]
        ];

        $ref = new \ReflectionProperty($this->plugin, 'capability');
        $ref->setAccessible(true);
        $ref->setValue($this->plugin, $capability);

        // Mock helper
        $this->helper->method('getOmiseCodeByOmiseId')
            ->willReturnMap([
                ['promptpay', 'omise_promptpay'],
                ['alipay', 'omise_alipay'],
                ['internet_banking', null],
            ]);

        // Call private method
        $method = new \ReflectionMethod($this->plugin, 'getPaymentMethods');
        $method->setAccessible(true);

        $result = $method->invoke($this->plugin);

        $this->assertEquals(
            ['omise_promptpay', 'omise_alipay'],
            $result
        );
    }

    /**
     * @covers \Omise\Payment\Plugin\ConfigSectionPaymentPlugin::getActivePaymentMethods
     */
    public function testGetActivePaymentMethods(): void
    {
        // Setup test config data
        $configData = [
            'groups' => [
                'alipay' => [
                    'fields' => [
                        'active' => ['value' => 1], // simple value
                    ],
                ],
                'promptpay' => [
                    'fields' => [
                        'active' => ['inherit' => 1], // inherit from parent scope
                    ],
                ],
                'truewallet' => [
                    'fields' => [
                        'active' => ['value' => 0], // inactive
                    ],
                ],
            ],
        ];

        // Setup parentScopeType for inherit logic
        $refScope = new \ReflectionProperty($this->plugin, 'parentScopeType');
        $refScope->setAccessible(true);
        $refScope->setValue($this->plugin, 'website');

        // Mock scopeConfig to return parent values
        $this->scopeConfig->method('getValue')
            ->with('payment/promptpay', 'website')
            ->willReturn(['active' => 1]);

        // Mock helper to return labels
        $this->helper->method('getOmiseLabelByOmiseCode')
            ->willReturnMap([
                ['alipay', 'Alipay Label'],
                ['promptpay', null], // fallback to config title
            ]);

        // Mock config->getValue fallback
        $this->config->method('getValue')
            ->with('title', 'promptpay')
            ->willReturn('PromptPay Title');

        // Call private method
        $method = new \ReflectionMethod($this->plugin, 'getActivePaymentMethods');
        $method->setAccessible(true);

        $result = $method->invoke($this->plugin, $configData);

        $expected = [
            'alipay' => 'Alipay Label',
            'promptpay' => 'PromptPay Title',
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers \Omise\Payment\Plugin\ConfigSectionPaymentPlugin::retrieveParentScope
     */
    public function testRetrieveParentScope(): void
    {
        // Use Reflection to access the private method
        $method = new \ReflectionMethod($this->plugin, 'retrieveParentScope');
        $method->setAccessible(true);

        // Default scope (getWebsite = null, getStore = null)
        $configDefault = $this->getMockBuilder(\Magento\Config\Model\Config::class)
            ->disableOriginalConstructor()
            ->addMethods(['getWebsite', 'getStore'])
            ->getMock();
        $configDefault->method('getWebsite')->willReturn(null);
        $configDefault->method('getStore')->willReturn(null);

        $this->assertNull($method->invoke($this->plugin, $configDefault));

        // Store scope (getWebsite null, getStore not null)
        $configStore = $this->getMockBuilder(\Magento\Config\Model\Config::class)
            ->disableOriginalConstructor()
            ->addMethods(['getWebsite', 'getStore'])
            ->getMock();
        $configStore->method('getWebsite')->willReturn(null);
        $configStore->method('getStore')->willReturn('store_1');

        $this->assertEquals('website', $method->invoke($this->plugin, $configStore));

        // Website scope (getWebsite not null)
        $configWebsite = $this->getMockBuilder(\Magento\Config\Model\Config::class)
            ->disableOriginalConstructor()
            ->addMethods(['getWebsite', 'getStore'])
            ->getMock();
        $configWebsite->method('getWebsite')->willReturn('website_1');
        $configWebsite->method('getStore')->willReturn(null);

        $this->assertEquals('default', $method->invoke($this->plugin, $configWebsite));
    }
}
