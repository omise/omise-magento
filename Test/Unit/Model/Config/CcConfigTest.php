<?php

namespace Omise\Payment\Test\Unit\Model\Config;

use Omise\Payment\Model\Config\Cc;
use PHPUnit\Framework\TestCase;

class CcConfigTest extends TestCase
{
    /**
     * @covers \Omise\Payment\Model\Config\Cc::getAllowedCardsIcon
     */
    public function testGetAllowedCardsIconReturnsConfiguredCommaSeparatedValues(): void
    {
        $config = $this->createConfigMock('visa,mastercard,amex');

        $this->assertSame(['visa', 'mastercard', 'amex'], $config->getAllowedCardsIcon());
    }

    /**
     * @covers \Omise\Payment\Model\Config\Cc::getAllowedCardsIcon
     */
    public function testGetAllowedCardsIconReturnsArrayConfigurationAsIs(): void
    {
        $allowedIcons = ['visa', 'mastercard'];
        $config = $this->createConfigMock($allowedIcons);

        $this->assertSame($allowedIcons, $config->getAllowedCardsIcon());
    }

    /**
     * @covers \Omise\Payment\Model\Config\Cc::getAllowedCardsIcon
     * @dataProvider emptyConfigValueProvider
     */
    public function testGetAllowedCardsIconReturnsEmptyArrayWhenNoIconsAreConfigured($emptyValue): void
    {
        $config = $this->createConfigMock($emptyValue);

        $this->assertSame([], $config->getAllowedCardsIcon());
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
            ->with('allow_card_icon', Cc::CODE)
            ->willReturn($value);

        return $config;
    }
}
