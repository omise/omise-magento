<?php

namespace Omise\Payment\Test\Unit\Model\Api;

use PHPUnit\Framework\TestCase;
use Omise\Payment\Model\Api\Charge;
use Omise\Payment\Model\Api\Error;
use Omise\Payment\Model\Config\Config;
use Magento\Framework\Exception\LocalizedException;

/**
 * @covers \Omise\Payment\Model\Api\Charge
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ChargeTest extends TestCase
{
    /** @var Config|\PHPUnit\Framework\MockObject\MockObject */
    private $configMock;

    /** @var Charge */
    private $charge;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->charge = new Charge($this->configMock);
    }

    private function setProtectedProperty($object, string $property, $value): void
    {
        $ref = new \ReflectionClass($object);
        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }

    public function testFindReturnsErrorOnException()
    {
        $this->configMock->method('setStoreId');

        $result = $this->charge->find('invalid_id');

        $this->assertInstanceOf(Error::class, $result);
    }

    public function testCreateThrowsLocalizedException()
    {
        $this->expectException(LocalizedException::class);
        $this->charge->create([]);
    }

    public function testCaptureReturnsError()
    {
        $mockObject = new class {
            public function capture()
            {
                throw new \Exception('capture failed');
            }
        };

        $this->setProtectedProperty($this->charge, 'object', $mockObject);

        $result = $this->charge->capture();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testGetMetadata()
    {
        $this->charge->metadata = ['order_id' => '1001'];

        $this->assertSame('1001', $this->charge->getMetadata('order_id'));
        $this->assertNull($this->charge->getMetadata('missing'));
    }

    public function testAuthorizationChecks()
    {
        $this->charge->authorized = true;

        $this->assertTrue($this->charge->isAuthorized());
        $this->assertFalse($this->charge->isUnauthorized());
    }

    public function testPaymentStates()
    {
        $this->charge->paid = true;

        $this->assertTrue($this->charge->isPaid());
        $this->assertFalse($this->charge->isUnpaid());
    }

    public function testAwaitCapture()
    {
        $this->charge->status = 'pending';
        $this->charge->authorized = true;
        $this->charge->paid = false;
        $this->charge->captured = false;

        $this->assertTrue($this->charge->isAwaitCapture());
    }

    public function testAwaitPayment()
    {
        $this->charge->status = 'pending';
        $this->charge->authorized = false;
        $this->charge->paid = false;
        $this->charge->captured = false;

        $this->assertTrue($this->charge->isAwaitPayment());
    }

    public function testSuccessfulCharge()
    {
        $this->charge->status = 'successful';
        $this->charge->paid = true;

        $this->assertTrue($this->charge->isSuccessful());
    }

    public function testFailedCharge()
    {
        $this->charge->status = 'failed';

        $this->assertTrue($this->charge->isFailed());
    }

    public function testRefundedAmountCalculation()
    {
        $this->charge->refunds = [
            'data' => [
                ['amount' => 500],
                ['amount' => 500],
            ],
        ];

        $this->assertSame(10, $this->charge->getRefundedAmount());
    }

    public function testFullyRefunded()
    {
        $this->charge->amount = 1000;
        $this->charge->refunds = [
            'data' => [
                ['amount' => 1000],
            ],
        ];

        $this->assertTrue($this->charge->isFullyRefunded());
    }

    public function testGetAmount()
    {
        $this->charge->amount = 5000; // set the property
        $this->assertSame(5000, $this->charge->getAmount());
    }

    public function testGetRefundedAmount()
    {
        // Case 1: refunds is null
        $this->charge->refunds = null;
        $this->assertSame(0, $this->charge->getRefundedAmount());

        // Case 2: refunds data exists
        $this->charge->refunds = [
            'data' => [
                ['amount' => 500],
                ['amount' => 1500],
            ],
        ];

        // Calculation: (500 + 1500) / 100 = 20
        $this->assertSame(20, $this->charge->getRefundedAmount());
    }

    public function testRefundReturnsValue()
    {
        // Step 1: Create a mock object with refund method
        $mockRefundObject = new class {
            public function refund($data)
            {
                return 'refund_success';
            }
        };

        // Step 2: Inject mock object into Charge using reflection
        $this->setProtectedProperty($this->charge, 'object', $mockRefundObject);

        // Step 3: Call refund() and assert return value
        $result = $this->charge->refund(['amount' => 100]);
        $this->assertSame('refund_success', $result);
    }

    public function testRefundThrowsLocalizedException()
    {
        // Step 1: Create a mock object that throws Exception on refund
        $mockRefundObject = new class {
            public function refund($data)
            {
                throw new \Exception('refund failed');
            }
        };

        // Step 2: Inject mock object into Charge using reflection
        $this->setProtectedProperty($this->charge, 'object', $mockRefundObject);

        // Step 3: Expect LocalizedException
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->charge->refund(['amount' => 100]);
    }

    public function testCaptureReturnsSelfOnSuccess()
    {
        // Step 1: Mock object with capture method
        $mockObject = new class {
            public function capture()
            {
                return 'captured';
            }
        };

        // Step 2: Inject mock object
        $this->setProtectedProperty($this->charge, 'object', $mockObject);

        // Step 3: Mock refresh() to avoid actual SDK call
        $chargeMock = $this->getMockBuilder(get_class($this->charge))
            ->onlyMethods(['refresh'])
            ->setConstructorArgs([$this->configMock])
            ->getMock();

        $chargeMock->setProtectedProperty = function($obj, $prop, $val) {
            $ref = new \ReflectionClass($obj);
            $property = $ref->getProperty($prop);
            $property->setAccessible(true);
            $property->setValue($obj, $val);
        };

        $chargeMock->expects($this->once())
            ->method('refresh')
            ->with('captured');

        $this->setProtectedProperty($chargeMock, 'object', $mockObject);

        $result = $chargeMock->capture();
        $this->assertSame($chargeMock, $result);
    }

    public function testCaptureReturnsErrorOnException()
    {
        // Step 1: Mock object that throws exception on capture
        $mockObject = new class {
            public function capture()
            {
                throw new \Exception('capture failed');
            }
        };

        $this->setProtectedProperty($this->charge, 'object', $mockObject);

        // Step 2: Call capture() → should return Error object
        $result = $this->charge->capture();

        $this->assertInstanceOf(\Omise\Payment\Model\Api\Error::class, $result);
    }
}
