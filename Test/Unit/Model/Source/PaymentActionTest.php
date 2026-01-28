<?php

namespace Omise\Payment\Test\Unit\Model\Source;

use Omise\Payment\Model\Source\PaymentAction;
use PHPUnit\Framework\TestCase;

class PaymentActionTest extends TestCase
{
    /**
     * @var PaymentAction
     */
    protected $paymentAction;

    /**
     * Set up the test
     */
    protected function setUp(): void
    {
        $this->paymentAction = new PaymentAction();
    }

    /**
     * @covers \Omise\Payment\Model\Source\PaymentAction::toOptionArray
     */
    public function testToOptionArrayReturnsCorrectOptions()
    {
        $expected = [
            [
                'value' => \Magento\Payment\Model\Method\Cc::ACTION_AUTHORIZE,
                'label' => 'Authorize Only',
            ],
            [
                'value' => \Magento\Payment\Model\Method\Cc::ACTION_AUTHORIZE_CAPTURE,
                'label' => 'Authorize and Capture'
            ]
        ];

        $actual = $this->paymentAction->toOptionArray();

        // Check that the returned array matches expected structure
        $this->assertIsArray($actual);
        $this->assertCount(2, $actual);
        $this->assertEquals($expected, $actual);
    }
}
