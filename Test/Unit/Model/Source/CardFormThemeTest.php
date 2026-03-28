<?php

namespace Omise\Payment\Test\Unit\Model\Source;

use Omise\Payment\Model\Source\CardFormTheme;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Omise\Payment\Model\Source\CardFormTheme
 */
class CardFormThemeTest extends TestCase
{
    /**
     * @var CardFormTheme
     */
    protected $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new CardFormTheme();
    }

    /**
     * @covers \Omise\Payment\Model\Source\CardFormTheme::toOptionArray
     */
    public function testToOptionArrayReturnsCorrectStructure()
    {
        $expected = [
            ['value' => 'dark', 'label' => 'Dark'],
            ['value' => 'light', 'label' => 'Light']
        ];

        $result = $this->model->toOptionArray();

        // Assert it returns an array
        $this->assertIsArray($result);

        // Assert the array matches expected
        $this->assertEquals($expected, $result);

        // Assert each element has 'value' and 'label' keys
        foreach ($result as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
        }
    }
}
