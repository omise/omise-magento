<?php
declare(strict_types=1);

namespace Omise\Payment\Test\Unit\Block\Adminhtml\System\Config\CardFormCustomization;

use Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme
 */
class ThemeTest extends TestCase
{
    private Theme $theme;

    protected function setUp(): void
    {
        $this->theme = new Theme();
    }

    /**
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme::getFormDesign
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme::getDefaultFormDesign
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme::getLightTheme
     */
    public function testGetFormDesignReturnsLightThemeWhenThemeIsEmpty(): void
    {
        $result = $this->theme->getFormDesign('', '');
        $this->assertSame($this->theme->getLightTheme(), $result);
    }

    /**
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme::getFormDesign
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme::getDefaultFormDesign
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme::getDarkTheme
     */
    public function testGetFormDesignReturnsDarkThemeWhenThemeIsDark(): void
    {
        $result = $this->theme->getFormDesign('dark', '');
        $this->assertSame($this->theme->getDarkTheme(), $result);
    }

    /**
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme::getFormDesign
     */
    public function testGetFormDesignReturnsCustomDesignWhenJsonIsValid(): void
    {
        $custom = [
            'font' => ['name' => 'Arial', 'size' => 20],
            'input' => ['height' => '50px'],
        ];

        $json = json_encode($custom);

        $result = $this->theme->getFormDesign('light', $json);

        $this->assertSame($custom, $result);
    }

    /**
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme::getFormDesign
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme::getDefaultFormDesign
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme::getLightTheme
     */
    public function testGetFormDesignFallsBackWhenJsonIsInvalid(): void
    {
        $result = $this->theme->getFormDesign('light', '{broken json');
        $this->assertSame($this->theme->getLightTheme(), $result);
    }

    /**
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme::getDefaultFormDesign
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme::getLightTheme
     */
    public function testGetDefaultFormDesignReturnsLight(): void
    {
        $this->assertSame(
            $this->theme->getLightTheme(),
            $this->theme->getDefaultFormDesign('light')
        );
    }

    /**
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme::getDefaultFormDesign
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme::getDarkTheme
     */
    public function testGetDefaultFormDesignReturnsDark(): void
    {
        $this->assertSame(
            $this->theme->getDarkTheme(),
            $this->theme->getDefaultFormDesign('dark')
        );
    }

    /**
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme::getLightTheme
     */
    public function testLightThemeHasExpectedStructure(): void
    {
        $light = $this->theme->getLightTheme();

        $this->assertArrayHasKey('font', $light);
        $this->assertArrayHasKey('input', $light);
        $this->assertArrayHasKey('checkbox', $light);
        $this->assertSame('#ffffff', $light['input']['background_color']);
    }

    /**
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\Theme::getDarkTheme
     */
    public function testDarkThemeHasExpectedStructure(): void
    {
        $dark = $this->theme->getDarkTheme();

        $this->assertArrayHasKey('font', $dark);
        $this->assertArrayHasKey('input', $dark);
        $this->assertArrayHasKey('checkbox', $dark);
        $this->assertSame('#131926', $dark['input']['background_color']);
    }
}
