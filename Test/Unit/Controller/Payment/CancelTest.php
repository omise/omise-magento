<?php

namespace Omise\Payment\Test\Unit\Controller\Payment;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Omise\Payment\Controller\Payment\Cancel;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Omise\Payment\Controller\Payment\Cancel
 */
class CancelTest extends TestCase
{
    /**
     * @covers \Omise\Payment\Controller\Payment\Cancel::__construct
     */
    public function testExecuteCancelsOrderSuccessfully()
    {
        $context = $this->createMock(Context::class);
        $checkoutSession = $this->getMockBuilder(CheckoutSession::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['restoreQuote'])
            ->addMethods(['getLastOrderId'])
            ->getMock();
        $orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $order = $this->createMock(Order::class);

        $checkoutSession->expects($this->once())
            ->method('getLastOrderId')
            ->willReturn(100);

        $orderRepository->expects($this->once())
            ->method('get')
            ->with(100)
            ->willReturn($order);

        $order->expects($this->once())
            ->method('getState')
            ->willReturn(Order::STATE_PENDING_PAYMENT);

        $order->expects($this->once())
            ->method('registerCancellation')
            ->with('Payment cancelled by customer (Omise cancel).');

        $orderRepository->expects($this->once())
            ->method('save')
            ->with($order);

        $checkoutSession->expects($this->once())->method('restoreQuote');

        $controller = $this->getMockBuilder(Cancel::class)
            ->setConstructorArgs([
                $context,
                $checkoutSession,
                $orderRepository,
                $logger
            ])
            ->onlyMethods(['_redirect'])
            ->getMock();

        $controller->expects($this->once())
            ->method('_redirect')
            ->with('checkout/cart', ['_secure' => true])
            ->willReturn($this->createMock(Redirect::class));

        $controller->execute();
    }

    /**
     * @covers \Omise\Payment\Controller\Payment\Cancel::__construct
     */
    public function testExecuteHandlesException()
    {
        $context = $this->createMock(Context::class);
        $checkoutSession = $this->getMockBuilder(CheckoutSession::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['restoreQuote'])
            ->addMethods(['getLastOrderId'])
            ->getMock();
        $orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $checkoutSession->expects($this->once())
            ->method('getLastOrderId')
            ->willReturn(100);

        $orderRepository->expects($this->once())
            ->method('get')
            ->with(100)
            ->willThrowException(new \Exception('Test exception'));

        $logger->expects($this->once())
            ->method('info')
            ->with('Order Cancel Error: Test exception');

        $checkoutSession->expects($this->once())
            ->method('restoreQuote');

        $controller = $this->getMockBuilder(Cancel::class)
            ->setConstructorArgs([
                $context,
                $checkoutSession,
                $orderRepository,
                $logger
            ])
            ->onlyMethods(['_redirect'])
            ->getMock();

        $controller->expects($this->once())
            ->method('_redirect')
            ->with('checkout/cart', ['_secure' => true])
            ->willReturn($this->createMock(Redirect::class));

        $controller->execute();
    }
}
