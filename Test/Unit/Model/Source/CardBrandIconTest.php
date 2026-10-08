<?php

namespace Omise\Payment\Test\Unit\Model\Source;

use Omise\Payment\Model\Source\CardBrandIcon;
use PHPUnit\Framework\TestCase;

class CardBrandIconTest extends TestCase
{
    /**
     * @covers \Omise\Payment\Model\Source\CardBrandIcon::toOptionArray
     */
    public function testToOptionArrayReturnsSupportedCardBrandIcons(): void
    {
        $source = new CardBrandIcon();
        $options = $source->toOptionArray();

        $this->assertSame(
            [
                ['value' => 'visa', 'label' => 'Visa'],
                ['value' => 'mastercard', 'label' => 'Mastercard'],
                ['value' => 'amex', 'label' => 'American Express'],
                ['value' => 'jcb', 'label' => 'JCB'],
                ['value' => 'diners', 'label' => 'Diners Club'],
                ['value' => 'discover', 'label' => 'Discover'],
            ],
            array_map(
                static function (array $option): array {
                    return [
                        'value' => $option['value'],
                        'label' => (string) $option['label'],
                    ];
                },
                $options
            )
        );
    }
}
