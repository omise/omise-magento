<?php

namespace Omise\Payment\Test\Unit\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\TransferInterface;
use Omise\Payment\Gateway\Http\Client\CCPayment;
use Omise\Payment\Model\Api\BaseObject;
use Omise\Payment\Model\Api\Charge;
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Model\Omise;
use OmiseCharge;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Omise\Payment\Gateway\Http\Client\CCPayment::placeRequest
 * @covers \Omise\Payment\Model\Api\Charge::capture
 * @uses \Omise\Payment\Gateway\Http\Client\AbstractPayment
 * @uses \Omise\Payment\Model\Api\BaseObject::refresh
 */
class CCPaymentTest extends TestCase
{
    public function testManualCaptureForwardsInvoiceAmountToOmise()
    {
        $sdkCharge = $this->createMock(OmiseCharge::class);
        $sdkCharge->expects($this->once())
            ->method('capture')
            ->with(['amount' => 5000])
            ->willReturn($sdkCharge);

        $apiCharge = new class($this->createMock(Config::class)) extends Charge {
            public function find($id, $storeId = null)
            {
                return $this;
            }

            public function setSdkCharge($sdkCharge)
            {
                $this->object = $sdkCharge;
            }
        };
        $apiCharge->setSdkCharge($sdkCharge);

        $omise = $this->createMock(Omise::class);
        $omise->method('defineUserAgent');
        $omise->method('defineApiVersion');
        $omise->method('defineApiKeys');

        $transfer = $this->createMock(TransferInterface::class);
        $transfer->method('getBody')->willReturn([
            'charge_id' => 'chrg_test_123',
            'amount' => 5000,
            'metadata' => ['store_id' => 1],
        ]);

        $client = new CCPayment($apiCharge, $omise);

        $this->assertSame(
            ['charge' => $apiCharge],
            $client->placeRequest($transfer)
        );
    }
}