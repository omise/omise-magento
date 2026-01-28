<?php

namespace Omise\Payment\Test\Unit\Model\Ui;

use Magento\Payment\Model\CcConfig as MagentoCcConfig;
use Omise\Payment\Model\Config\Cc as OmiseCcConfig;
use Omise\Payment\Model\Customer;
use Omise\Payment\Model\Ui\CcConfigProvider;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Omise\Payment\Model\Ui\CcConfigProvider
 */
class CcConfigProviderTest extends TestCase
{
    private $magentoCcConfig;
    private $omiseCcConfig;
    private $customer;
    private $provider;

    protected function setUp(): void
    {
        $this->magentoCcConfig = $this->createMock(MagentoCcConfig::class);
        $this->omiseCcConfig   = $this->createMock(OmiseCcConfig::class);
        $this->customer        = $this->createMock(Customer::class);

        $this->provider = new CcConfigProvider(
            $this->magentoCcConfig,
            $this->omiseCcConfig,
            $this->customer
        );
    }

    /**
     * @covers \Omise\Payment\Model\Ui\CcConfigProvider::getCards
     * @covers \Omise\Payment\Model\Ui\CcConfigProvider::getConfig
     * @covers \Omise\Payment\Model\Ui\CcConfigProvider::__construct
     */
    public function testGetCardsWhenCustomerNotLoggedIn()
    {
        $this->customer->method('getMagentoCustomerId')->willReturn(null);
        $this->customer->method('getId')->willReturn(null);

        $this->assertSame([], $this->provider->getCards());
    }

    /**
     * @covers \Omise\Payment\Model\Ui\CcConfigProvider::getCards
     * @covers \Omise\Payment\Model\Ui\CcConfigProvider::getConfig
     * @covers \Omise\Payment\Model\Ui\CcConfigProvider::__construct
     */
    public function testGetCardsWhenEmptyResponse()
    {
        $this->customer->method('getMagentoCustomerId')->willReturn(1);
        $this->customer->method('getId')->willReturn(2);

        $this->customer->method('cards')->willReturn(['data' => []]);

        $this->assertSame([], $this->provider->getCards());
    }

    /**
     * @covers ::__construct
     * @covers ::getCards
     */
    public function testGetCardsWhenInvalidJson()
    {
        $this->customer->method('getMagentoCustomerId')->willReturn(1);
        $this->customer->method('getId')->willReturn(2);

        // Instead of invalid JSON, return empty array to simulate invalid/malformed response
        $this->customer->method('cards')->willReturn(['data' => []]);

        $this->assertSame([], $this->provider->getCards());
    }

    /**
     * cards() returns false
     * Covers: if (!$cards) { return []; }
     *
     * @covers ::getCards
     * @covers ::__construct
     */
    public function testGetCardsWhenCardsApiReturnsFalse()
    {
        $this->customer->method('getMagentoCustomerId')->willReturn(1);
        $this->customer->method('getId')->willReturn(2);

        $this->customer->method('cards')->willReturn(false);

        $this->assertSame([], $this->provider->getCards());
    }

    /**
     * cards() returns valid card data
     *
     * @covers ::getCards
     * @covers ::__construct
     */
    public function testGetCardsWithValidCards()
    {
        $this->customer->method('getMagentoCustomerId')->willReturn(1);
        $this->customer->method('getId')->willReturn(2);

        $this->customer->method('cards')->with(
            ['order' => 'reverse_chronological']
        )->willReturn([
            'data' => [
                [
                    'id' => 'card_123',
                    'brand' => 'Visa',
                    'last_digits' => '4242',
                ]
            ]
        ]);

        $expected = [
            [
                'value' => 'card_123',
                'label' => 'Visa **** 4242',
            ]
        ];

        $this->assertSame($expected, $this->provider->getCards());
    }

    /**
     * @covers ::getConfig
     * @uses \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme::getFormDesign
     * @covers ::__construct
     * @covers ::getCards
     */
    public function testGetConfigReturnsExpectedStructure()
    {
        // Magento CC config
        $this->magentoCcConfig->method('getCcMonths')->willReturn([
            '01' => '01',
            '02' => '02',
        ]);

        $this->magentoCcConfig->method('getCcYears')->willReturn([
            '2026' => '2026',
            '2027' => '2027',
        ]);

        // Omise config (IMPORTANT FIX HERE)
        $this->omiseCcConfig->method('getPublicKey')->willReturn('pkey_test');
        $this->omiseCcConfig->method('getCardThemeConfig')->willReturn(
            json_encode(['color' => 'blue'])
        );
        $this->omiseCcConfig->method('getCardTheme')->willReturn('default');
        $this->omiseCcConfig->method('getStoreLocale')->willReturn('en');

        // Customer
        $this->customer->method('isLoggedIn')->willReturn(true);

        // Avoid card API execution
        $this->customer->method('getMagentoCustomerId')->willReturn(null);
        $this->customer->method('getId')->willReturn(null);

        $config = $this->provider->getConfig();

        // Assertions
        $this->assertArrayHasKey('payment', $config);
        $this->assertArrayHasKey('ccform', $config['payment']);
        $this->assertArrayHasKey(OmiseCcConfig::CODE, $config['payment']);

        $paymentConfig = $config['payment'][OmiseCcConfig::CODE];

        $this->assertSame('pkey_test', $paymentConfig['publicKey']);
        $this->assertTrue($paymentConfig['isCustomerLoggedIn']);
        $this->assertSame([], $paymentConfig['cards']);
        $this->assertSame('en', $paymentConfig['locale']);
        $this->assertSame('default', $paymentConfig['theme']);

        // Theme output exists
        $this->assertArrayHasKey('formDesign', $paymentConfig);
    }
}
