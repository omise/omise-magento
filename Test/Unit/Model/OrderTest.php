<?php

namespace Omise\Payment\Test\Unit\Model;

use Omise\Payment\Model\Order as OmiseOrder;
use Magento\Sales\Model\Order as MagentoOrder;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    /**
     * @var MagentoOrder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $magentoOrderMock;

    /**
     * @var OmiseOrder
     */
    private $orderModel;

    protected function setUp(): void
    {
        $this->magentoOrderMock = $this->createMock(MagentoOrder::class);
        $this->orderModel = new OmiseOrder($this->magentoOrderMock);
    }

    /**
     * @covers \Omise\Payment\Model\Order::__construct
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(OmiseOrder::class, $this->orderModel);
    }

    /**
     * @covers \Omise\Payment\Model\Order::loadByIncrementId
     * @covers \Omise\Payment\Model\Order::__construct
     */
    public function testLoadByIncrementId(): void
    {
        $incrementId = '100000001';

        // Expect the mock's loadByIncrementId to be called and return itself
        $this->magentoOrderMock
            ->expects($this->once())
            ->method('loadByIncrementId')
            ->with($incrementId)
            ->willReturn($this->magentoOrderMock);

        $result = $this->orderModel->loadByIncrementId($incrementId);

        $this->assertSame($this->magentoOrderMock, $result);
    }
}
