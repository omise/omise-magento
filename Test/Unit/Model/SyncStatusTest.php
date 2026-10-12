<?php

namespace Omise\Payment\Test\Unit\Model;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\Payment;
use Omise\Payment\Helper\OmiseEmailHelper;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Model\Config\Cc;
use Omise\Payment\Model\RefundSyncStatus;
use Omise\Payment\Model\SyncStatus;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class SyncStatusTest extends TestCase
{
    /**
     * @covers \Omise\Payment\Model\SyncStatus
     */
    public function testSuccessfulManualSyncStoresChargeIdOnPayment(): void
    {
        $helper = $this->createMock(OmiseHelper::class);
        $emailHelper = $this->createMock(OmiseEmailHelper::class);
        $config = $this->createMock(Cc::class);
        $refundSyncStatus = $this->createMock(RefundSyncStatus::class);
        $syncStatus = new SyncStatus($helper, $emailHelper, $config, $refundSyncStatus);

        $charge = ['id' => 'ch_manual_sync_123'];
        $refundSyncStatus->expects($this->once())
            ->method('shouldRefund')
            ->with($charge)
            ->willReturn(false);

        $orderConfig = $this->createMock(OrderConfig::class);
        $orderConfig->method('getStateDefaultStatus')
            ->with(Order::STATE_PROCESSING)
            ->willReturn('processing');

        $payment = $this->createMock(Payment::class);
        $payment->expects($this->once())
            ->method('setTransactionId')
            ->with($charge['id']);
        $payment->expects($this->once())
            ->method('setLastTransId')
            ->with($charge['id']);
        $payment->expects($this->once())
            ->method('setAdditionalInformation')
            ->with('charge_id', $charge['id']);

        $order = $this->createMock(Order::class);
        $order->method('getState')->willReturn(Order::STATE_PENDING_PAYMENT);
        $order->method('getConfig')->willReturn($orderConfig);
        $order->method('getPayment')->willReturn($payment);
        $order->method('getGrandTotal')->willReturn(25.00);
        $order->method('getOrderCurrencyCode')->willReturn('THB');
        $order->expects($this->once())->method('setState')->with(Order::STATE_PROCESSING);
        $order->expects($this->once())->method('setStatus')->with('processing');
        $order->expects($this->once())->method('addStatusHistoryComment');
        $order->expects($this->once())->method('save');

        $helper->expects($this->once())
            ->method('createInvoiceAndMarkAsPaid')
            ->with($order, $charge['id']);
        $emailHelper->expects($this->once())
            ->method('sendInvoiceAndConfirmationEmails')
            ->with($order);

        $method = new ReflectionMethod(SyncStatus::class, 'markPaymentSuccessful');
        $method->setAccessible(true);
        $method->invoke($syncStatus, $order, $charge);
    }
}
