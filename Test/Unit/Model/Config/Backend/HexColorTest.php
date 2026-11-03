<?php

namespace Omise\Payment\Test\Unit\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ActionValidator\RemoveAction;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Omise\Payment\Model\Config\Backend\HexColor;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class HexColorTest extends TestCase
{
    /**
     * @covers \Omise\Payment\Model\Config\Backend\HexColor::beforeSave
     * @dataProvider validColorProvider
     */
    public function testBeforeSaveAcceptsBlankAndValidHexColors($value): void
    {
        $backend = $this->createBackend($value);

        $this->assertSame($backend, $backend->beforeSave());
    }

    public static function validColorProvider(): array
    {
        return [
            'null is treated as blank' => [null],
            'empty string' => [''],
            'whitespace is treated as blank' => [" \t\n"],
            'three digit hex' => ['#abc'],
            'six digit hex' => ['#1979C3'],
            'mixed case hex' => ['#aF09cD'],
        ];
    }

    /**
     * @covers \Omise\Payment\Model\Config\Backend\HexColor::beforeSave
     * @dataProvider invalidColorProvider
     */
    public function testBeforeSaveRejectsInvalidHexColors($value): void
    {
        $backend = $this->createBackend($value);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Please enter a valid HEX color (Example: #1979C3).');

        $backend->beforeSave();
    }

    public static function invalidColorProvider(): array
    {
        return [
            'missing hash' => ['1979C3'],
            'unsupported length' => ['#12345'],
            'non hexadecimal character' => ['#12GG45'],
            'trailing text' => ['#123abc extra'],
        ];
    }

    private function createBackend($value): HexColor
    {
        $eventManager = $this->createMock(ManagerInterface::class);
        $context = new Context(
            $this->createMock(LoggerInterface::class),
            $eventManager,
            $this->createMock(CacheInterface::class),
            $this->createMock(State::class),
            $this->createMock(RemoveAction::class)
        );

        return new HexColor(
            $context,
            new Registry(),
            $this->createMock(ScopeConfigInterface::class),
            $this->createMock(TypeListInterface::class),
            null,
            null,
            ['value' => $value]
        );
    }
}
