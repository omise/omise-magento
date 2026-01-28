<?php

namespace Omise\Payment\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Omise\Payment\Gateway\Request\CreditCardThreeDSecureBuilder;
use Omise\Payment\Helper\ReturnUrlHelper;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Omise\Payment\Gateway\Request\CreditCardThreeDSecureBuilder
 */
class CreditCardThreeDSecureBuilderTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::build
     */
    public function testBuildReturnsReturnUriAndSetsPaymentToken()
    {
        /** -------- Mocks -------- */
        $returnUrlHelper = $this->createMock(ReturnUrlHelper::class);
        $paymentDO       = $this->createMock(PaymentDataObjectInterface::class);
        $payment         = $this->createMock(Payment::class);

        /** -------- Return URL helper -------- */
        $returnUrlHelper->expects($this->once())
            ->method('create')
            ->with('omise/callback/threedsecure')
            ->willReturn([
                'url'   => 'https://example.com/3ds',
                'token' => 'token_123'
            ]);

        /** -------- Payment data object -------- */
        $paymentDO->expects($this->once())
            ->method('getPayment')
            ->willReturn($payment);

        /** -------- Payment expectations -------- */
        $payment->expects($this->once())
            ->method('setAdditionalInformation')
            ->with('token', 'token_123');

        /** -------- Execute -------- */
        $builder = new CreditCardThreeDSecureBuilder($returnUrlHelper);

        $result = $builder->build([
            'payment' => $paymentDO
        ]);

        /** -------- Assertions -------- */
        $this->assertSame(
            [
                CreditCardThreeDSecureBuilder::RETURN_URI => 'https://example.com/3ds'
            ],
            $result
        );
    }
}
