<?php

namespace Omise\Payment\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Model\Data\Email;
use Omise\Payment\Observer\PaymentCreationObserver;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Omise\Payment\Observer\PaymentCreationObserver
 */
class PaymentCreationObserverTest extends TestCase
{
    private $helper;
    private $email;
    private $observerClass;

    protected function setUp(): void
    {
        $this->helper = $this->createMock(OmiseHelper::class);
        $this->email  = $this->createMock(Email::class);

        $this->observerClass = new PaymentCreationObserver(
            $this->helper,
            $this->email
        );
    }

    private function buildObserver($paymentMethod)
    {
        $payment = $this->createMock(Payment::class);
        $payment->method('getMethod')->willReturn($paymentMethod);

        $order = $this->createMock(Order::class);
        $order->method('getPayment')->willReturn($payment);

        $event = new Event(['order' => $order]);

        return [$order, new Observer(['event' => $event])];
    }

    /**
     * Branch 1: NOT an Omise payment
     */
    public function testExecuteWithNonOmisePayment()
    {
        [$order, $observer] = $this->buildObserver('checkmo');

        $this->helper->method('isOmisePayment')->willReturn(false);
        $this->helper->method('isOfflinePaymentMethod')->willReturn(false);

        $order->expects($this->never())
            ->method('setCanSendNewEmailFlag');

        $this->email->expects($this->never())
            ->method('sendEmail');

        $this->observerClass->execute($observer);
    }

    /**
     * Branch 2: Omise but NOT offline
     */
    public function testExecuteWithOmiseOnlinePayment()
    {
        [$order, $observer] = $this->buildObserver('omise_cc');

        $this->helper->method('isOmisePayment')->willReturn(true);
        $this->helper->method('isOfflinePaymentMethod')->willReturn(false);

        $order->expects($this->once())
            ->method('setCanSendNewEmailFlag')
            ->with(false);

        $this->email->expects($this->never())
            ->method('sendEmail');

        $this->observerClass->execute($observer);
    }

    /**
     * Branch 3: Omise + Offline
     */
    public function testExecuteWithOfflineOmisePayment()
    {
        [$order, $observer] = $this->buildObserver('omise_promptpay');

        $this->helper->method('isOmisePayment')->willReturn(true);
        $this->helper->method('isOfflinePaymentMethod')->willReturn(true);

        $order->expects($this->once())
            ->method('setCanSendNewEmailFlag')
            ->with(false);

        $this->email->expects($this->once())
            ->method('sendEmail')
            ->with($order);

        $this->observerClass->execute($observer);
    }
}
