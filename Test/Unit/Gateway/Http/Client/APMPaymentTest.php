<?php

namespace Omise\Payment\Test\Unit\Gateway\Http\Client;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Omise\Payment\Gateway\Http\Client\APMPayment;
use Omise\Payment\Model\Api\Charge as ApiCharge;
use Omise\Payment\Model\Api\CheckoutSession;
use Omise\Payment\Model\Omise;
use Magento\Payment\Gateway\Http\TransferInterface;

class APMPaymentTest extends TestCase
{
    private $apiChargeMock;
    private $omiseMock;
    private $checkoutSessionMock;
    private $transferObjectMock;
    private $apmPayment;

    protected function setUp(): void
    {
        $this->apiChargeMock = m::mock(ApiCharge::class);
        $this->omiseMock = m::mock(Omise::class);
        $this->checkoutSessionMock = m::mock(CheckoutSession::class);
        $this->transferObjectMock = m::mock(TransferInterface::class);

        $this->apmPayment = new APMPayment(
            $this->apiChargeMock,
            $this->omiseMock,
            $this->checkoutSessionMock
        );
    }

    /**
     * Test constructor initializes dependencies correctly
     * @covers Omise\Payment\Gateway\Http\Client\APMPayment::__construct
     */
    public function testConstructor()
    {
        $apmPayment = new APMPayment(
            $this->apiChargeMock,
            $this->omiseMock,
            $this->checkoutSessionMock
        );

        $this->assertInstanceOf(APMPayment::class, $apmPayment);
    }

    /**
     * Test placeRequest with is_upa parameter calls checkout session
     * @covers Omise\Payment\Gateway\Http\Client\APMPayment::placeRequest
     */
    public function testPlaceRequestWithIsUPA()
    {
        $transferBody = ['is_upa' => true, 'amount' => 1000];
        $sessionResponse = ['id' => 'session_123', 'object' => 'checkout_session'];

        $this->transferObjectMock->shouldReceive('getBody')
            ->andReturn($transferBody);

        $this->checkoutSessionMock->shouldReceive('createSession')
            ->with($transferBody)
            ->andReturn($sessionResponse);

        $result = $this->apmPayment->placeRequest($this->transferObjectMock);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('session', $result);
        $this->assertEquals($sessionResponse, $result['session']);
    }

    /**
     * Test placeRequest without is_upa parameter calls charge create
     * @covers Omise\Payment\Gateway\Http\Client\APMPayment::placeRequest
     */
    public function testPlaceRequestWithoutIsUPA()
    {
        $transferBody = ['amount' => 1000, 'currency' => 'THB'];
        $chargeResponse = ['id' => 'charge_123', 'object' => 'charge'];

        $this->transferObjectMock->shouldReceive('getBody')
            ->andReturn($transferBody);

        $this->apiChargeMock->shouldReceive('create')
            ->with($transferBody)
            ->andReturn($chargeResponse);

        $result = $this->apmPayment->placeRequest($this->transferObjectMock);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('charge', $result);
        $this->assertEquals($chargeResponse, $result['charge']);
    }

    /**
     * Test placeRequest with empty is_upa still calls charge create
     * @covers Omise\Payment\Gateway\Http\Client\APMPayment::placeRequest
     */
    public function testPlaceRequestWithEmptyIsUPA()
    {
        $transferBody = ['is_upa' => false, 'amount' => 1000];
        $chargeResponse = ['id' => 'charge_456', 'object' => 'charge'];

        $this->transferObjectMock->shouldReceive('getBody')
            ->andReturn($transferBody);

        $this->apiChargeMock->shouldReceive('create')
            ->with($transferBody)
            ->andReturn($chargeResponse);

        $result = $this->apmPayment->placeRequest($this->transferObjectMock);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('charge', $result);
        $this->assertEquals($chargeResponse, $result['charge']);
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
