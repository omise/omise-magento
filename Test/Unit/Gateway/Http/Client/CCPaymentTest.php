<?php

namespace Omise\Payment\Test\Unit\Gateway\Http\Client;

use Omise\Payment\Gateway\Http\Client\CCPayment;
use Omise\Payment\Gateway\Http\Client\AbstractPayment;
use Omise\Payment\Model\Api\Charge;
use Omise\Payment\Model\Omise;
use Magento\Payment\Gateway\Http\TransferInterface;
use PHPUnit\Framework\TestCase;

class CCPaymentTest extends TestCase
{
    /** @var CCPayment */
    private $ccPayment;

    /** @var Charge */
    private $apiChargeMock;

    /** @var Omise */
    private $omiseMock;

    /** @var TransferInterface */
    private $transferMock;

    protected function setUp(): void
    {
        // Mock API Charge
        $this->apiChargeMock = $this->getMockBuilder(Charge::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create', 'find'])
            ->getMock();

        // Mock Omise dependency
        $this->omiseMock = $this->getMockBuilder(Omise::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Instantiate CCPayment with proper mocks
        $this->ccPayment = new CCPayment($this->apiChargeMock, $this->omiseMock);

        // Mock TransferInterface
        $this->transferMock = $this->createMock(TransferInterface::class);
    }

    /**
     * @covers \Omise\Payment\Gateway\Http\Client\CCPayment::placeRequest
     * @covers \Omise\Payment\Gateway\Http\Client\AbstractPayment::__construct
     */
    public function testPlaceRequestCreatesChargeIfNoChargeId(): void
    {
        $body = ['amount' => 1000, 'currency' => 'THB'];
        $this->transferMock->method('getBody')->willReturn($body);

        $chargeMock = $this->getMockBuilder(Charge::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->apiChargeMock->expects($this->once())
            ->method('create')
            ->with($body)
            ->willReturn($chargeMock);

        $result = $this->ccPayment->placeRequest($this->transferMock);

        $this->assertSame([CCPayment::CHARGE => $chargeMock], $result);
    }

    /**
     * @covers \Omise\Payment\Gateway\Http\Client\CCPayment::placeRequest
     * @covers \Omise\Payment\Gateway\Http\Client\AbstractPayment::__construct
     */
    public function testPlaceRequestCapturesChargeIfChargeIdExists(): void
    {
        $body = [
            CCPayment::CHARGE_ID => 'ch_test',
            'metadata' => ['store_id' => 1]
        ];
        $this->transferMock->method('getBody')->willReturn($body);

        $chargeMock = $this->getMockBuilder(Charge::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['capture'])
            ->getMock();

        $chargeMock->expects($this->once())
            ->method('capture')
            ->willReturn('captured_charge');

        $this->apiChargeMock->expects($this->once())
            ->method('find')
            ->with('ch_test', 1)
            ->willReturn($chargeMock);

        $result = $this->ccPayment->placeRequest($this->transferMock);

        $this->assertSame([CCPayment::CHARGE => 'captured_charge'], $result);
    }
}
