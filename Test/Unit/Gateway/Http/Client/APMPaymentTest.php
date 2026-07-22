<?php

namespace Omise\Payment\Test\Unit\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\TransferInterface;
use Omise\Payment\Gateway\Http\Client\APMPayment;
use Omise\Payment\Model\Api\Charge as ApiCharge;
use Omise\Payment\Model\Api\CheckoutSession;
use Omise\Payment\Model\Omise;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Omise\Payment\Gateway\Http\Client\APMPayment
 * @uses \Omise\Payment\Gateway\Http\Client\AbstractPayment
 */
class APMPaymentTest extends TestCase
{
    private const CHARGE_ID = 'chrg_test_123';
    private const SESSION_ID = 'sess_test_123';
    /**
     * @covers ::__construct
     * @covers ::placeRequest
     */
    public function testPlaceRequestCreatesSession()
    {
        $apiCharge = $this->createMock(ApiCharge::class);

        $omise = $this->createMock(Omise::class);
        $omise->method('defineUserAgent');
        $omise->method('defineApiVersion');
        $omise->method('defineApiKeys');

        $checkoutSession = $this->createMock(CheckoutSession::class);
        $transfer = $this->createMock(TransferInterface::class);

        $body = [
            'is_upa' => true,
            'amount' => 1000
        ];

        $transfer->method('getBody')
            ->willReturn($body);

        $checkoutSession->expects($this->once())
            ->method('createSession')
            ->with($body)
            ->willReturn(self::SESSION_ID);

        $client = new APMPayment(
            $apiCharge,
            $omise,
            $checkoutSession
        );

        $result = $client->placeRequest($transfer);

        $this->assertSame(
            ['session' => self::SESSION_ID],
            $result
        );
    }

    /**
     * @covers \Omise\Payment\Gateway\Http\Client\APMPayment::__construct
     * @covers \Omise\Payment\Gateway\Http\Client\APMPayment::placeRequest
     * @uses \Omise\Payment\Gateway\Http\Client\AbstractPayment
     */
    public function testPlaceRequestCreatesCharge()
    {
        $apiCharge = $this->createMock(ApiCharge::class);

        $omise = $this->createMock(Omise::class);
        $omise->method('defineUserAgent');
        $omise->method('defineApiVersion');
        $omise->method('defineApiKeys');

        $checkoutSession = $this->createMock(CheckoutSession::class);
        $transfer = $this->createMock(TransferInterface::class);

        $body = [
            'amount' => 1000
        ];

        $transfer->method('getBody')
            ->willReturn($body);

        $apiCharge->expects($this->once())
            ->method('create')
            ->with($body)
            ->willReturn(self::CHARGE_ID);

        $client = new APMPayment(
            $apiCharge,
            $omise,
            $checkoutSession
        );

        $result = $client->placeRequest($transfer);

        $this->assertSame(
            ['charge' => self::CHARGE_ID],
            $result
        );
    }
}
