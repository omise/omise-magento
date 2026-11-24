<?php

namespace Omise\Payment\Test\Unit\Model;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config as OrderConfig;
 use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface as TransactionBuilderInterface;
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
    public function testSuccessfulManualSyncCreatesTransactionWithSessionId(): void
    {
        $helper = $this->createMock(OmiseHelper::class);
        $emailHelper = $this->createMock(OmiseEmailHelper::class);
        $config = $this->createMock(Cc::class);
        $refundSyncStatus = $this->createMock(RefundSyncStatus::class);
        $transactionBuilder = $this->createMock(TransactionBuilderInterface::class);
        $syncStatus = new SyncStatus(
            $helper,
            $emailHelper,
            $config,
            $refundSyncStatus,
            $transactionBuilder
        );

        $charge = [
            'id' => 'ch_manual_sync_123',
            'status' => 'successful',
            'amount' => 2500,
            'currency' => 'thb',
        ];
        $refundSyncStatus->expects($this->once())
            ->method('shouldRefund')
            ->with($charge)
            ->willReturn(false);
        $helper->expects($this->once())
            ->method('getChargeTransactionDetails')
            ->with($charge, 'session_manual_sync_123')
            ->willReturn([
                'charge_id' => $charge['id'],
                'status' => $charge['status'],
                'amount' => (string) $charge['amount'],
                'currency' => $charge['currency'],
                'session_id' => 'session_manual_sync_123',
            ]);

        $orderConfig = $this->createMock(OrderConfig::class);
        $orderConfig->method('getStateDefaultStatus')
            ->with(Order::STATE_PROCESSING)
            ->willReturn('processing');

        $payment = $this->createMock(Payment::class);
        $payment->expects($this->once())
            ->method('getAdditionalInformation')
            ->with('session_id')
            ->willReturn('session_manual_sync_123');
        $payment->expects($this->once())
            ->method('setTransactionId')
            ->with($charge['id']);
        $payment->expects($this->once())
            ->method('setLastTransId')
            ->with($charge['id']);
        $payment->expects($this->once())
            ->method('setAdditionalInformation')
            ->with('charge_id', $charge['id']);
        $invoice = $this->createMock(Invoice::class);
        $transaction = $this->createMock(Transaction::class);
        $transactionBuilder->expects($this->once())
            ->method('setPayment')
            ->with($payment)
            ->willReturnSelf();
        $transactionBuilder->expects($this->once())
            ->method('setOrder')
            ->with($order = $this->createMock(Order::class))
            ->willReturnSelf();
        $transactionBuilder->expects($this->once())
            ->method('setTransactionId')
            ->with($charge['id'])
            ->willReturnSelf();
        $transactionBuilder->expects($this->once())
            ->method('setAdditionalInformation')
            ->with([
                Transaction::RAW_DETAILS => [
                    'charge_id' => $charge['id'],
                    'status' => $charge['status'],
                    'amount' => (string) $charge['amount'],
                    'currency' => $charge['currency'],
                    'session_id' => 'session_manual_sync_123',
                ],
            ])
            ->willReturnSelf();
        $transactionBuilder->expects($this->once())
            ->method('setFailSafe')
            ->with(true)
            ->willReturnSelf();
        $transactionBuilder->expects($this->once())
            ->method('build')
            ->with(Transaction::TYPE_PAYMENT)
            ->willReturn($transaction);
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
            ->with($order, $charge['id'])
            ->willReturn($invoice);
        $emailHelper->expects($this->once())
            ->method('sendInvoiceAndConfirmationEmails')
            ->with($order);

        $method = new ReflectionMethod(SyncStatus::class, 'markPaymentSuccessful');
        $method->setAccessible(true);
        $method->invoke($syncStatus, $order, $charge);
    }
}
