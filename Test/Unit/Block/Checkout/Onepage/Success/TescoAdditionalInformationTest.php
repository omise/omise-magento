<?php
declare(strict_types=1);

namespace Omise\Payment\Test\Unit\Block\Checkout\Onepage\Success;

use Omise\Payment\Block\Checkout\Onepage\Success\TescoAdditionalInformation;
use PHPUnit\Framework\TestCase;

class TescoAdditionalInformationTest extends TestCase
{
    /**
     * Safely invoke protected _toHtml() without Magento rendering pipeline
     */
    private function invokeToHtml(TescoAdditionalInformation $block)
    {
        $ref = new \ReflectionClass($block);
        $method = $ref->getMethod('_toHtml');
        $method->setAccessible(true);

        return $method->invoke($block);
    }

    /**
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\TescoAdditionalInformation::_toHtml
     */
    public function testToHtmlReturnsNullWhenNotTesco(): void
    {
        $block = $this->getMockBuilder(TescoAdditionalInformation::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPaymentType'])
            ->getMock();

        $block->method('getPaymentType')->willReturn('truemoney');

        $result = $this->invokeToHtml($block);

        // In this branch, _toHtml() returns NULL
        $this->assertNull($result);
    }

    /**
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\TescoAdditionalInformation::_toHtml
     */
    public function testToHtmlForTesco(): void
    {
        $block = $this->getMockBuilder(TescoAdditionalInformation::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getPaymentType',
                'getOrderAmount',
                'getPaymentAdditionalInformation',
                'addData'
            ])
            ->getMock();

        $block->method('getPaymentType')->willReturn('bill_payment_tesco_lotus');
        $block->method('getOrderAmount')->willReturn(900);
        $block->method('getPaymentAdditionalInformation')
            ->with('barcode')
            ->willReturn('TESCO999');

        // This line gives us coverage on addData()
        $block->expects($this->once())->method('addData')->with([
            'order_amount' => 900,
            'offline_code' => 'TESCO999'
        ]);

        $result = $this->invokeToHtml($block);

        // parent::_toHtml() returns empty string in Magento
        $this->assertSame('', $result);
    }
}
