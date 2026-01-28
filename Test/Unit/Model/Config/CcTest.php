<?php
namespace Omise\Payment\Test\Unit\Model\Config;

use Omise\Payment\Model\Config\Cc;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Omise\Payment\Model\Config\Cc
 */
class CcTest extends TestCase
{
    /**
     * @var Cc
     */
    protected $cc;

    protected function setUp(): void
    {
        $this->cc = $this->getMockBuilder(Cc::class)
            ->onlyMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->cc->method('getValue')->willReturnCallback(function ($key, $code) {
            return "{$key}_{$code}";
        });
    }

    /**
     * @covers \Omise\Payment\Model\Config\Cc::__construct
     * @covers \Omise\Payment\Model\Config\Cc::getCardThemeConfig
     */
    public function testGetCardThemeConfig()
    {
        $result = $this->cc->getCardThemeConfig();
        $this->assertEquals('card_form_theme_config_omise_cc', $result);
    }

    /**
     * @covers \Omise\Payment\Model\Config\Cc::__construct
     * @covers \Omise\Payment\Model\Config\Cc::getCardTheme
     */
    public function testGetCardTheme()
    {
        $result = $this->cc->getCardTheme();
        $this->assertEquals('card_form_theme_omise_cc', $result);
    }

    /**
     * @coversNothing
     */
    public function testConstants()
    {
        $this->assertEquals('omise_cc', Cc::CODE);
        $this->assertEquals('card', Cc::ID);
    }
}
