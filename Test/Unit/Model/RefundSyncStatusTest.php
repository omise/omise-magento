<?php

namespace Omise\Payment\Test\Unit\Model;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Omise\Payment\Model\RefundSyncStatus;
use Omise\Payment\Service\CreditMemoService;
use PHPUnit\Framework\TestCase;

class RefundSyncStatusTest extends TestCase
{
    /**
     * @covers \Omise\Payment\Model\RefundSyncStatus
     */
    public function testRefundCreatesCreditMemoAndAddsHistoryComment(): void
    {
        $creditMemoService = $this->createMock(CreditMemoService::class);
        $orderConfig = $this->createMock(OrderConfig::class);
        $orderConfig->method('getStateDefaultStatus')
            ->with(Order::STATE_PROCESSING)
            ->willReturn('processing');

        $order = $this->createMock(Order::class);
        $order->method('getConfig')->willReturn($orderConfig);
        $order->method('getOrderCurrencyCode')->willReturn('THB');
        $order->expects($this->once())->method('setState')->with(Order::STATE_PROCESSING);
        $order->expects($this->once())->method('setStatus')->with('processing');
        $order->expects($this->once())->method('addStatusHistoryComment');
        $order->expects($this->once())->method('save');

        $creditMemoService->expects($this->once())->method('create')->with($order, 5.0);

        $refundSyncStatus = new RefundSyncStatus($creditMemoService);
        $refundSyncStatus->refund($order, ['refunded_amount' => 500]);
    }
}
