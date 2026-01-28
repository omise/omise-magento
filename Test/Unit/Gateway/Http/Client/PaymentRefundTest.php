<?php

namespace Omise\Payment\Test\Unit\Gateway\Http\Client;

use Omise\Payment\Gateway\Http\Client\PaymentRefund;
use Omise\Payment\Model\Api\Charge as ApiCharge;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;

class PaymentRefundTest extends TestCase
{
    private $apiChargeMock;
    private $transferMock;
    private $paymentRefund;

    protected function setUp(): void
    {
        $this->apiChargeMock = $this->createMock(ApiCharge::class);
        $this->transferMock = $this->createMock(TransferInterface::class);

        $this->paymentRefund = $this->getMockBuilder(PaymentRefund::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        // Inject $apiCharge into protected property
        $reflection = new \ReflectionClass($this->paymentRefund);
        $property = $reflection->getProperty('apiCharge');
        $property->setAccessible(true);
        $property->setValue($this->paymentRefund, $this->apiChargeMock);
    }

    /**
     * @covers \Omise\Payment\Gateway\Http\Client\PaymentRefund::placeRequest
     */
    public function testPlaceRequestSuccess()
    {
        $body = [
            'transaction_id' => 'chrg_test_123',
            'store_id' => 1,
            'amount' => 1000
        ];

        $this->transferMock->method('getBody')->willReturn($body);

        $chargeMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['refund'])
            ->getMock();

        $chargeMock->refundable = true;
        $chargeMock->expects($this->once())
            ->method('refund')
            ->with(['amount' => 1000])
            ->willReturn('refund_success');

        $this->apiChargeMock->expects($this->once())
            ->method('find')
            ->with('chrg_test_123', 1)
            ->willReturn($chargeMock);

        $result = $this->paymentRefund->placeRequest($this->transferMock);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('refund', $result);
        $this->assertSame('refund_success', $result['refund']);
    }

    /**
     * @covers \Omise\Payment\Gateway\Http\Client\PaymentRefund::placeRequest
     */
    public function testPlaceRequestThrowsExceptionWhenTransactionIdMissing()
    {
        $this->transferMock->method('getBody')->willReturn(['store_id' => 1]);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Unable to process refund.');

        $this->paymentRefund->placeRequest($this->transferMock);
    }

    /**
     * @covers \Omise\Payment\Gateway\Http\Client\PaymentRefund::placeRequest
     */
    public function testPlaceRequestThrowsExceptionWhenNotRefundable()
    {
        $body = [
            'transaction_id' => 'chrg_test_123',
            'store_id' => 1,
            'amount' => 1000
        ];

        $this->transferMock->method('getBody')->willReturn($body);

        $chargeMock = new \stdClass();
        $chargeMock->refundable = false;
        $chargeMock->source = ['type' => 'truemoney_jumpapp'];

        $this->apiChargeMock->expects($this->once())
            ->method('find')
            ->with('chrg_test_123', 1)
            ->willReturn($chargeMock);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Payment with Omise truemoney jumpapp cannot be refunded.');

        $this->paymentRefund->placeRequest($this->transferMock);
    }
}