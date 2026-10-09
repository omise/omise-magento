<?php

namespace Omise\Payment\Test\Unit\Controller\Callback;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Omise\Payment\Controller\Callback\UPACallback;
use Omise\Payment\Model\Omise;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Omise\Payment\Controller\Callback\UPACallback
 */
class UPACallbackTest extends TestCase
{
    private $context;
    private $session;
    private $omise;
    private $messageManager;

    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->session = $this->createMock(Session::class);
        $this->omise = $this->createMock(Omise::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);

        $this->context->method('getMessageManager')
            ->willReturn($this->messageManager);
    }

    private function getController()
    {
        return $this->getMockBuilder(UPACallback::class)
            ->setConstructorArgs([$this->context, $this->session, $this->omise])
            ->onlyMethods(['_redirect'])
            ->getMock();
    }

    /**
     * @covers ::execute
     */
    public function testExecuteRedirectsValidPendingOrderToSuccess(): void
    {
        $payment = $this->createMock(Payment::class);
        $order = $this->createConfiguredMock(Order::class, [
            'getId' => 10,
            'getPayment' => $payment,
            'getState' => Order::STATE_PENDING_PAYMENT,
        ]);
        $this->session->method('getLastRealOrder')->willReturn($order);

        $redirect = $this->createMock(Redirect::class);
        $controller = $this->getController();
        $controller->expects($this->once())
            ->method('_redirect')
            ->with('checkout/onepage/success', ['_secure' => true])
            ->willReturn($redirect);

        $this->assertSame($redirect, $controller->execute());
    }

    /**
     * @covers ::execute
     */
    public function testExecuteRedirectsProcessingOrderToSuccess(): void
    {
        $payment = $this->createMock(Payment::class);
        $order = $this->createConfiguredMock(Order::class, [
            'getId' => 10,
            'getPayment' => $payment,
            'getState' => Order::STATE_PROCESSING,
        ]);
        $this->session->method('getLastRealOrder')->willReturn($order);

        $redirect = $this->createMock(Redirect::class);
        $controller = $this->getController();
        $controller->expects($this->once())
            ->method('_redirect')
            ->with('checkout/onepage/success', ['_secure' => true])
            ->willReturn($redirect);

        $this->assertSame($redirect, $controller->execute());
    }

    /**
     * @covers ::execute
     */
    public function testExecuteRedirectsToCartWhenOrderIsMissing(): void
    {
        $order = $this->createConfiguredMock(Order::class, ['getId' => null]);
        $this->session->method('getLastRealOrder')->willReturn($order);
        $redirect = $this->createMock(Redirect::class);

        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $controller = $this->getController();
        $controller->expects($this->once())
            ->method('_redirect')
            ->with('checkout/cart', ['_secure' => true])
            ->willReturn($redirect);

        $this->assertSame($redirect, $controller->execute());
    }

    /**
     * @covers ::execute
     */
    public function testExecuteRedirectsToCartWhenPaymentIsMissing(): void
    {
        $order = $this->createConfiguredMock(Order::class, [
            'getId' => 10,
            'getPayment' => null,
        ]);
        $this->session->method('getLastRealOrder')->willReturn($order);
        $redirect = $this->createMock(Redirect::class);

        $order->expects($this->once())->method('addStatusHistoryComment');
        $order->expects($this->once())->method('save');
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $controller = $this->getController();
        $controller->expects($this->once())
            ->method('_redirect')
            ->with('checkout/cart', ['_secure' => true])
            ->willReturn($redirect);

        $this->assertSame($redirect, $controller->execute());
    }
}
