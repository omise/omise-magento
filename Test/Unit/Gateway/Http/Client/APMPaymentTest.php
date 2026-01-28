<?php

namespace Omise\Payment\Test\Unit\Gateway\Http\Client;

use Omise\Payment\Gateway\Http\Client\APMPayment;
use Omise\Payment\Model\Api\Charge as ApiCharge;
use Magento\Payment\Gateway\Http\TransferInterface;
use PHPUnit\Framework\TestCase;

class APMPaymentTest extends TestCase
{
    private $apiChargeMock;
    private $transferMock;
    private $apmPayment;

    protected function setUp(): void
    {
        $this->apiChargeMock = $this->createMock(ApiCharge::class);
        $this->transferMock = $this->createMock(TransferInterface::class);

        $this->apmPayment = $this->getMockBuilder(APMPayment::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        // Inject the mock $apiCharge into the protected property
        $reflection = new \ReflectionClass($this->apmPayment);
        $property = $reflection->getProperty('apiCharge');
        $property->setAccessible(true);
        $property->setValue($this->apmPayment, $this->apiChargeMock);
    }

    /**
     * @covers \Omise\Payment\Gateway\Http\Client\APMPayment::placeRequest
     */
    public function testPlaceRequestReturnsChargeArray()
    {
        $requestBody = ['amount' => 1000, 'currency' => 'THB'];

        $this->transferMock->method('getBody')
            ->willReturn($requestBody);

        $apiChargeResponse = new \stdClass();
        $apiChargeResponse->id = 'chrg_test_123';

        $this->apiChargeMock->expects($this->once())
            ->method('create')
            ->with($requestBody)
            ->willReturn($apiChargeResponse);

        $result = $this->apmPayment->placeRequest($this->transferMock);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('charge', $result);
        $this->assertSame($apiChargeResponse, $result['charge']);
    }
}
