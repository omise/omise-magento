<?php

namespace Omise\Payment\Test\Unit\Gateway\Response;

use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Omise\Payment\Gateway\Response\RefundHandler;
use Magento\Payment\Gateway\Helper\SubjectReader;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Omise\Payment\Gateway\Response\RefundHandler
 */
class RefundHandlerTest extends TestCase
{
    private $handler;

    protected function setUp(): void
    {
        // Use real SubjectReader instance; no static mock needed
        $this->handler = new RefundHandler(new SubjectReader());
    }

    /**
     * @covers ::__construct
     * @covers ::handle
     * @covers ::shouldCloseTransaction
     * @covers ::shouldCloseParentTransaction
     */
    public function testHandleClosesTransactionAndParent(): void
    {
        $paymentMock = $this->createMock(Payment::class);
        $invoiceMock = $this->createMock(Invoice::class);
        $creditmemoMock = $this->createMock(Creditmemo::class);

        $invoiceMock->method('canRefund')->willReturn(false);
        $creditmemoMock->method('getInvoice')->willReturn($invoiceMock);
        $paymentMock->method('getCreditmemo')->willReturn($creditmemoMock);

        $paymentMock->expects($this->once())
            ->method('setIsTransactionClosed')
            ->with(true);

        $paymentMock->expects($this->once())
            ->method('setShouldCloseParentTransaction')
            ->with(true);

        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $paymentDO->method('getPayment')->willReturn($paymentMock);

        $handlingSubject = ['payment' => $paymentDO];
        $response = [];

        $this->handler->handle($handlingSubject, $response);
    }

    /**
     * @covers ::__construct
     * @covers ::handle
     */
    public function testHandleDoesNothingIfNotPaymentInstance(): void
    {
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $paymentDO->method('getPayment')->willReturn(null);

        $handlingSubject = ['payment' => $paymentDO];
        $response = [];

        $this->assertNull($this->handler->handle($handlingSubject, $response));
    }

    /**
     * @covers ::__construct
     * @covers ::shouldCloseTransaction
     */
    public function testShouldCloseTransaction(): void
    {
        $reflection = new \ReflectionClass($this->handler);
        $method = $reflection->getMethod('shouldCloseTransaction');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($this->handler));
    }

    /**
     * @covers ::__construct
     * @covers ::shouldCloseParentTransaction
     */
    public function testShouldCloseParentTransaction(): void
    {
        $reflection = new \ReflectionClass($this->handler);
        $method = $reflection->getMethod('shouldCloseParentTransaction');
        $method->setAccessible(true);

        // Branch 1: canRefund() = true → parent transaction NOT closed
        $paymentMock1 = $this->createMock(Payment::class);
        $invoiceMock1 = $this->createMock(Invoice::class);
        $creditmemoMock1 = $this->createMock(Creditmemo::class);

        $invoiceMock1->method('canRefund')->willReturn(true);
        $creditmemoMock1->method('getInvoice')->willReturn($invoiceMock1);
        $paymentMock1->method('getCreditmemo')->willReturn($creditmemoMock1);

        $this->assertFalse($method->invoke($this->handler, $paymentMock1));

        // Branch 2: canRefund() = false → parent transaction closed
        $paymentMock2 = $this->createMock(Payment::class);
        $invoiceMock2 = $this->createMock(Invoice::class);
        $creditmemoMock2 = $this->createMock(Creditmemo::class);

        $invoiceMock2->method('canRefund')->willReturn(false);
        $creditmemoMock2->method('getInvoice')->willReturn($invoiceMock2);
        $paymentMock2->method('getCreditmemo')->willReturn($creditmemoMock2);

        $this->assertTrue($method->invoke($this->handler, $paymentMock2));
    }
}
