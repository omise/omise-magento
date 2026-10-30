<?php

namespace Omise\Payment\Test\Unit\Observer\WebhookObserver;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Directory\Model\Currency;
use Omise\Payment\Helper\OmiseEmailHelper;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Model\Api\Charge;
use Omise\Payment\Model\Api\Event;
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Model\Order as OrderModel;
use Omise\Payment\Observer\WebhookObserver\WebhookCompleteObserver;
use Omise\Payment\Observer\WebhookObserver\WebhookObserver;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;

class WebhookCompleteObserverTest extends TestCase
{
    /**
     * @covers \Omise\Payment\Observer\WebhookObserver\WebhookCompleteObserver
     * @uses \Omise\Payment\Model\Api\BaseObject
     * @uses \Omise\Payment\Model\Api\Charge
     * @uses \Omise\Payment\Observer\WebhookObserver\WebhookObserver
     * @dataProvider sessionPaymentProvider
     */
    public function testSuccessfulProcessingStoresChargeIdentifiersOnlyForSessionPayments(bool $hasSessionId): void
    {
        $helper = $this->createMock(OmiseHelper::class);
        $invoice = $this->createMock(Invoice::class);
        $invoice->method('getBaseGrandTotal')->willReturn(12.50);
        $helper->expects($this->once())
            ->method('createInvoiceAndMarkAsPaid')
            ->with($this->isInstanceOf(Order::class), 'ch_webhook_123', true)
            ->willReturn($invoice);

        $emailHelper = $this->createMock(OmiseEmailHelper::class);
        $config = $this->createMock(Config::class);
        $apiEvent = $this->getMockBuilder(Event::class)->disableOriginalConstructor()->getMock();
        $orderModel = $this->getMockBuilder(OrderModel::class)->disableOriginalConstructor()->getMock();
        $observer = new WebhookCompleteObserver($apiEvent, $orderModel, $emailHelper, $helper, $config);

        $charge = new Charge($config);
        $refresh = new ReflectionMethod(Charge::class, 'refresh');
        $refresh->setAccessible(true);
        $refresh->invoke($charge, ['id' => 'ch_webhook_123']);

        $currency = $this->createMock(Currency::class);
        $currency->method('formatTxt')->with(12.50)->willReturn('12.50');
        $orderConfig = $this->createMock(OrderConfig::class);
        $orderConfig->method('getStateDefaultStatus')
            ->with(Order::STATE_PROCESSING)
            ->willReturn('processing');

        $transaction = $this->createMock(Transaction::class);
        $payment = $this->createMock(Payment::class);
        $payment->method('getAdditionalInformation')
            ->with('session_id')
            ->willReturn($hasSessionId ? 'sess_123' : null);
        $payment->expects($hasSessionId ? $this->once() : $this->never())
            ->method('setTransactionId')
            ->with('ch_webhook_123');
        $payment->expects($hasSessionId ? $this->once() : $this->never())
            ->method('setLastTransId')
            ->with('ch_webhook_123');
        $payment->expects($hasSessionId ? $this->once() : $this->never())
            ->method('setAdditionalInformation')
            ->with('charge_id', 'ch_webhook_123');
        $payment->expects($this->once())
            ->method('addTransaction')
            ->with(Transaction::TYPE_PAYMENT, $invoice)
            ->willReturn($transaction);
        $payment->expects($this->once())->method('addTransactionCommentsToOrder');

        $order = $this->createMock(Order::class);
        $order->method('getConfig')->willReturn($orderConfig);
        $order->method('getBaseCurrency')->willReturn($currency);
        $order->expects($this->once())->method('setState')->with(Order::STATE_PROCESSING);
        $order->expects($this->once())->method('setStatus')->with('processing');
        $order->method('getPayment')->willReturn($payment);
        $order->expects($this->once())->method('save');

        $emailHelper->expects($this->once())
            ->method('sendInvoiceAndConfirmationEmails')
            ->with($order);

        $this->setObserverProperty($observer, 'payment', $payment);
        $this->setObserverProperty($observer, 'charge', $charge);
        $this->setObserverProperty($observer, 'orderData', $order);

        $processOrder = new ReflectionMethod(WebhookCompleteObserver::class, 'processOrder');
        $processOrder->setAccessible(true);
        $processOrder->invoke($observer);

        $invoiceProperty = new ReflectionProperty(WebhookObserver::class, 'invoice');
        $invoiceProperty->setAccessible(true);
        $this->assertSame($invoice, $invoiceProperty->getValue($observer));
    }

    public static function sessionPaymentProvider(): array
    {
        return [
            'checkout session payment' => [true],
            'non session payment' => [false],
        ];
    }

    private function setObserverProperty(WebhookCompleteObserver $observer, string $propertyName, $value): void
    {
        $property = new ReflectionProperty(WebhookObserver::class, $propertyName);
        $property->setAccessible(true);
        $property->setValue($observer, $value);
    }
}
