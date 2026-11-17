<?php

namespace Omise\Payment\Test\Unit\Service;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Service\CreditmemoService as MagentoCreditmemoService;
use Omise\Payment\Service\CreditMemoService;
use PHPUnit\Framework\TestCase;

class CreditMemoServiceTest extends TestCase
{
    /**
     * @covers \Omise\Payment\Service\CreditMemoService
     */
    public function testCreateRecordsOnlyNewRefundAmountAsOfflineAdjustment(): void
    {
        $orderItem = $this->createMock(Item::class);
        $orderItem->method('getId')->willReturn(42);

        $order = $this->createMock(Order::class);
        $order->method('getTotalRefunded')->willReturn(2.0);
        $order->method('getBaseTotalRefunded')->willReturn(1.0);
        $order->method('getBaseTotalPaid')->willReturn(100.0);
        $order->method('getBaseToOrderRate')->willReturn(2.0);
        $order->method('getAllItems')->willReturn([$orderItem]);
        $order->method('getIncrementId')->willReturn('100000001');
        $order->method('getOrderCurrencyCode')->willReturn('THB');

        $payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->addMethods(['setMessage'])
            ->getMock();
        $payment->expects($this->once())
            ->method('setMessage')
            ->with('We refunded THB 3.00 online via Omise.');
        $order->method('getPayment')->willReturn($payment);

        $creditMemo = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->addMethods(['setCustomerNote', 'setCustomerNoteNotify'])
            ->onlyMethods([
                'addComment',
                'setBaseAdjustmentPositive',
                'setBaseAdjustment',
                'setBaseGrandTotal'
            ])
            ->getMock();
        $creditMemo->expects($this->once())->method('setCustomerNote');
        $creditMemo->expects($this->once())->method('setCustomerNoteNotify')->with(false);
        $creditMemo->expects($this->once())->method('setBaseAdjustmentPositive')->with(1.5);
        $creditMemo->expects($this->once())->method('setBaseAdjustment')->with(1.5);
        $creditMemo->expects($this->once())->method('setBaseGrandTotal')->with(1.5);
        $creditMemo->expects($this->once())->method('addComment');

        $creditMemoFactory = $this->createMock(CreditmemoFactory::class);
        $creditMemoFactory->expects($this->once())
            ->method('createByOrder')
            ->with($order, [
                'qtys' => [42 => 0],
                'adjustment_positive' => 3.0
            ])
            ->willReturn($creditMemo);

        $magentoCreditMemoService = $this->createMock(MagentoCreditmemoService::class);
        $magentoCreditMemoService->expects($this->once())
            ->method('refund')
            ->with($creditMemo);

        $service = new CreditMemoService($creditMemoFactory, $magentoCreditMemoService);
        $service->create($order, 5.0);
    }

    /**
     * @covers \Omise\Payment\Service\CreditMemoService
     */
    public function testCreateDoesNotDuplicatePreviouslyRecordedRefund(): void
    {
        $order = $this->createMock(Order::class);
        $order->method('getTotalRefunded')->willReturn(5.0);

        $creditMemoFactory = $this->createMock(CreditmemoFactory::class);
        $creditMemoFactory->expects($this->never())->method('createByOrder');

        $magentoCreditMemoService = $this->createMock(MagentoCreditmemoService::class);
        $magentoCreditMemoService->expects($this->never())->method('refund');

        $service = new CreditMemoService($creditMemoFactory, $magentoCreditMemoService);
        $service->create($order, 5.0);
    }
}
