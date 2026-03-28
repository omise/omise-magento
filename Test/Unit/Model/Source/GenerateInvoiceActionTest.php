<?php

namespace Omise\Payment\Test\Unit\Model\Source;

use Omise\Payment\Model\Source\GenerateInvoiceAction;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Omise\Payment\Model\Source\GenerateInvoiceAction
 */
class GenerateInvoiceActionTest extends TestCase
{
    /**
     * @var GenerateInvoiceAction
     */
    private $generateInvoiceAction;

    protected function setUp(): void
    {
        $this->generateInvoiceAction = new GenerateInvoiceAction();
    }

    /**
     * @covers \Omise\Payment\Model\Source\GenerateInvoiceAction::toOptionArray
     */
    public function testToOptionArrayReturnsCorrectArray()
    {
        $expected = [
            [
                'value' => Order::STATE_PENDING_PAYMENT,
                'label' => 'Pending Payment (Default)',
            ],
            [
                'value' => Order::STATE_PROCESSING,
                'label' => 'Processing',
            ]
        ];

        $result = $this->generateInvoiceAction->toOptionArray();

        // Check the array structure
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals($expected, $result);
    }
}
