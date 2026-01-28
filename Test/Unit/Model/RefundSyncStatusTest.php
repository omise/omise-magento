<?php

namespace Omise\Payment\Test\Unit\Model;

use Omise\Payment\Model\RefundSyncStatus;
use Omise\Payment\Service\CreditMemoService;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Omise\Payment\Model\RefundSyncStatus
 */
class RefundSyncStatusTest extends TestCase
{
    private $creditMemoService;
    private $refundSyncStatus;
    private $order;

    protected function setUp(): void
    {
        $this->creditMemoService = $this->createMock(CreditMemoService::class);
        $this->refundSyncStatus = new RefundSyncStatus($this->creditMemoService);

        $this->order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'setState',
                'setStatus',
                'getConfig',
                'getOrderCurrencyCode',
                'addStatusHistoryComment',
                'save'
            ])
            ->getMock();

        $configMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStateDefaultStatus'])
            ->getMock();
        $configMock->method('getStateDefaultStatus')->willReturn('processing_default');

        $this->order->method('getConfig')->willReturn($configMock);
        $this->order->method('getOrderCurrencyCode')->willReturn('THB');
    }

    public function testShouldRefundReturnsTrueWhenRefundsExist(): void
    {
        $charge = ['refunds' => ['data' => [['id' => 're_123']]]];
        $this->assertTrue($this->refundSyncStatus->shouldRefund($charge));
    }

    public function testShouldRefundReturnsFalseWhenNoRefunds(): void
    {
        $charge = ['refunds' => ['data' => []]];
        $this->assertFalse($this->refundSyncStatus->shouldRefund($charge));

        $charge = [];
        $this->assertFalse($this->refundSyncStatus->shouldRefund($charge));
    }

    public function testRefundUpdatesOrderAndAddsComment(): void
    {
        $charge = ['refunded_amount' => 12345]; // 123.45 THB

        $this->order->expects($this->once())->method('setState')->with(Order::STATE_PROCESSING);
        $this->order->expects($this->once())->method('setStatus')->with('processing_default');
        $this->order->expects($this->once())
            ->method('addStatusHistoryComment')
            ->with(
                'Opn Payments: Payment refunded.<br/>An amount of 123.45 THB has been refunded (manual sync).'
            );
        $this->order->expects($this->once())->method('save');

        $this->refundSyncStatus->refund($this->order, $charge);
    }
}
