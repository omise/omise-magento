<?php

namespace Omise\Payment\Test\Unit\Model\Config;

use Omise\Payment\Model\Config\Cc;
use PHPUnit\Framework\TestCase;

class CcConfigTest extends TestCase
{
    /**
     * @covers \Omise\Payment\Model\Config\Cc::getSupportedCardIcons
     */
    public function testGetSupportedCardIconsReturnsConfiguredCommaSeparatedValues(): void
    {
        $config = $this->createConfigMock('visa,mastercard,amex');

        $this->assertSame(['visa', 'mastercard', 'amex'], $config->getSupportedCardIcons());
    }

    /**
     * @covers \Omise\Payment\Model\Config\Cc::getSupportedCardIcons
     */
    /**
     * @covers \Omise\Payment\Model\Config\Cc::getSupportedCardIcons
     * @dataProvider emptyConfigValueProvider
     */
    public function testGetSupportedCardIconsReturnsEmptyArrayWhenNoIconsAreConfigured($emptyValue): void
    {
        $config = $this->createConfigMock($emptyValue);

        $this->assertSame([], $config->getSupportedCardIcons());
    }

    public static function emptyConfigValueProvider(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
        ];
    }

    private function createConfigMock($value): Cc
    {
        $config = $this->getMockBuilder(Cc::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue'])
            ->getMock();

        $config->expects($this->once())
            ->method('getValue')
            ->with('supported_card_icons', Cc::CODE)
            ->willReturn($value);

        return $config;
    }
}
