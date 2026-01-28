<?php

namespace Omise\Payment\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Directory\Model\Currency;
use Omise\Payment\Gateway\Request\RefundDataBuilder;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Helper\OmiseMoney;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Omise\Payment\Gateway\Request\RefundDataBuilder
 */
class RefundDataBuilderTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::build
     */
    public function testBuildReturnsExpectedRefundPayload()
    {
        $subjectReader = new SubjectReader();
        $omiseHelper   = $this->createMock(OmiseHelper::class);
        $money         = $this->createMock(OmiseMoney::class);

        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $payment   = $this->createMock(Payment::class);
        $order     = $this->createMock(Order::class);
        $store     = $this->createMock(StoreInterface::class);
        $currency  = $this->createMock(Currency::class);

        $paymentDO->method('getPayment')->willReturn($payment);
        $payment->method('getOrder')->willReturn($order);
        $payment->method('getParentTransactionId')->willReturn('txn_123');

        $order->method('getStore')->willReturn($store);
        $store->method('getId')->willReturn(1);

        $order->method('getOrderCurrency')->willReturn($currency);
        $currency->method('getCode')->willReturn('THB');

        $order->method('getTotalOnlineRefunded')->willReturn(100.50);

        $money->expects($this->once())
            ->method('setAmountAndCurrency')
            ->with(100.50, 'THB')
            ->willReturnSelf();

        $money->expects($this->once())
            ->method('toSubunit')
            ->willReturn(10050);

        $builder = new RefundDataBuilder(
            $subjectReader,
            $omiseHelper,
            $money
        );

        $result = $builder->build([
            'payment' => $paymentDO
        ]);

        $this->assertSame(
            [
                'store_id'        => 1,
                'transaction_id' => 'txn_123',
                'amount'         => 10050
            ],
            $result
        );
    }
}