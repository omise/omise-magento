<?php

namespace Omise\Payment\Test\Unit\Model\Api;

use Omise\Payment\Model\Api\Event;
use Omise\Payment\Model\Api\Charge;
use Omise\Payment\Model\Api\Error;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class EventTest extends TestCase
{
    private $chargeMock;

    protected function setUp(): void
    {
        $this->chargeMock = $this->createMock(Charge::class);
    }

    /**
     * @covers \Omise\Payment\Model\Api\Event::__construct
     */
    public function testConstructor(): void
    {
        $event = new Event($this->chargeMock);
        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * @covers \Omise\Payment\Model\Api\Event::transformDataToObject
     * @covers \Omise\Payment\Model\Api\Event::__construct
     */
    public function testTransformDataToObjectForCharge(): void
    {
        $event = new Event($this->chargeMock);

        $this->chargeMock->expects($this->once())
            ->method('find')
            ->with('chrg_test_123')
            ->willReturn(['object' => 'charge', 'id' => 'chrg_test_123']);

        $reflection = new ReflectionClass($event);
        $method = $reflection->getMethod('transformDataToObject');
        $method->setAccessible(true);

        $result = $method->invoke($event, ['object' => 'charge', 'id' => 'chrg_test_123']);
        $this->assertSame(['object' => 'charge', 'id' => 'chrg_test_123'], $result);
    }

    /**
     * @covers \Omise\Payment\Model\Api\Event::transformDataToObject
     * @covers \Omise\Payment\Model\Api\Event::__construct
     */
    public function testTransformDataToObjectForRefund(): void
    {
        $event = new Event($this->chargeMock);

        $this->chargeMock->expects($this->once())
            ->method('find')
            ->with('chrg_test_456')
            ->willReturn(['object' => 'charge', 'id' => 'chrg_test_456']);

        $reflection = new ReflectionClass($event);
        $method = $reflection->getMethod('transformDataToObject');
        $method->setAccessible(true);

        $result = $method->invoke($event, ['object' => 'refund', 'charge' => 'chrg_test_456']);
        $this->assertSame(['object' => 'charge', 'id' => 'chrg_test_456'], $result);
    }

    /**
     * @covers \Omise\Payment\Model\Api\Event::transformDataToObject
     * @covers \Omise\Payment\Model\Api\Event::__construct
     */
    public function testTransformDataToObjectReturnsOriginalForUnknownObject(): void
    {
        $event = new Event($this->chargeMock);

        $data = ['object' => 'customer', 'id' => 'cust_123'];

        $reflection = new ReflectionClass($event);
        $method = $reflection->getMethod('transformDataToObject');
        $method->setAccessible(true);

        $result = $method->invoke($event, $data);
        $this->assertSame($data, $result);
    }

}
