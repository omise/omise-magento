<?php

namespace Omise\Payment\Test\Unit\Gateway\Response;

use Magento\Sales\Model\Order;
use Omise\Payment\Gateway\Response\PendingPaymentHandler;
use Omise\Payment\Helper\OmiseHelper;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Omise\Payment\Gateway\Response\PendingPaymentHandler
 */
class PendingPaymentHandlerTest extends TestCase
{
    /** @var PendingPaymentHandler */
    private $handler;

    /** @var OmiseHelper */
    private $helper;

    protected function setUp(): void
    {
        $this->helper = $this->createMock(OmiseHelper::class);
        $this->handler = new PendingPaymentHandler($this->helper);
    }

    /**
     * @covers ::__construct
     * @covers ::handle
     */
    public function testEarlyReturnIfNot3DSecure(): void
    {
        $handlingSubject = [];
        $response = ['charge' => []];

        $this->helper->method('is3DSecureEnabled')->willReturn(false);

        $this->assertNull($this->handler->handle($handlingSubject, $response));
    }

    /**
     * @covers ::__construct
     * @covers ::handle
     */
    public function testNormalPathSetsPendingPaymentState(): void
    {
        $response = ['charge' => []];

        $stateObject = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->addMethods(['setState', 'setStatus', 'setIsNotified'])
            ->getMock();

        $stateObject->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_PENDING_PAYMENT);
        $stateObject->expects($this->once())
            ->method('setStatus')
            ->with(Order::STATE_PENDING_PAYMENT);
        $stateObject->expects($this->once())
            ->method('setIsNotified')
            ->with(false);

        $handlingSubject = ['stateObject' => $stateObject];

        $this->helper->method('is3DSecureEnabled')->willReturn(true);

        $this->handler->handle($handlingSubject, $response);
    }
}
