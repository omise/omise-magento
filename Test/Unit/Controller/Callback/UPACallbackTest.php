<?php

namespace Omise\Payment\Test\Unit\Controller\Callback;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Omise\Payment\Controller\Callback\UPACallback;
use Omise\Payment\Model\Api\Charge;
use Omise\Payment\Model\Api\CheckoutSession as OmiseCheckoutSession;
use Omise\Payment\Model\Omise;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \Omise\Payment\Controller\Callback\UPACallback */
class UPACallbackTest extends TestCase
{
    private $context;
    private $session;
    private $omise;
    private $charge;
    private $checkoutSession;
    private $omiseCheckoutSession;
    private $messageManager;

    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->session = $this->createMock(CheckoutSession::class);
        $this->omise = $this->createMock(Omise::class);
        $this->charge = $this->createMock(Charge::class);
        $this->checkoutSession = $this->createMock(CheckoutSession::class);
        $this->omiseCheckoutSession = $this->createMock(OmiseCheckoutSession::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);

        $this->context->method('getMessageManager')->willReturn($this->messageManager);
        $this->omise->expects($this->once())->method('defineUserAgent');
        $this->omise->expects($this->once())->method('defineApiVersion');
        $this->omise->expects($this->once())->method('defineApiKeys');
    }

    private function getController()
    {
        return $this->getMockBuilder(UPACallback::class)
            ->setConstructorArgs([
                $this->context,
                $this->session,
                $this->omise,
                $this->charge,
                $this->checkoutSession,
                $this->omiseCheckoutSession,
            ])
            ->onlyMethods(['_redirect'])
            ->getMock();
    }

    private function order($state = Order::STATE_PENDING_PAYMENT, $payment = null)
    {
        if ($payment === null) {
            $payment = $this->createMock(Payment::class);
        }
        return $this->createConfiguredMock(Order::class, [
            'getId' => 10,
            'getPayment' => $payment,
            'getState' => $state,
        ]);
    }

    private function expectRedirect($controller, $path)
    {
        $redirect = $this->createMock(Redirect::class);
        $controller->expects($this->once())->method('_redirect')
            ->with($path, ['_secure' => true])->willReturn($redirect);
        return $redirect;
    }

    /** @covers \Omise\Payment\Controller\Callback\UPACallback */
    public function testProcessingOrderRedirectsDirectlyToSuccess(): void
    {
        $this->session->method('getLastRealOrder')->willReturn($this->order(Order::STATE_PROCESSING));
        $controller = $this->getController();
        $redirect = $this->expectRedirect($controller, UPACallback::PATH_SUCCESS);
        $this->assertSame($redirect, $controller->execute());
    }

    /** @covers \Omise\Payment\Controller\Callback\UPACallback */
    public function testMissingOrderRedirectsToCart(): void
    {
        $order = $this->createConfiguredMock(Order::class, ['getId' => null]);
        $this->session->method('getLastRealOrder')->willReturn($order);
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $controller = $this->getController();
        $redirect = $this->expectRedirect($controller, UPACallback::PATH_CART);
        $this->assertSame($redirect, $controller->execute());
    }

    /** @covers \Omise\Payment\Controller\Callback\UPACallback */
    public function testMissingPaymentIsInvalidAndRedirectsToCart(): void
    {
        $order = $this->createConfiguredMock(Order::class, [
            'getId' => 10,
            'getPayment' => null,
            'getState' => Order::STATE_PENDING_PAYMENT,
        ]);
        $order->expects($this->once())->method('addStatusHistoryComment');
        $order->expects($this->once())->method('save');
        $this->session->method('getLastRealOrder')->willReturn($order);
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $controller = $this->getController();
        $redirect = $this->expectRedirect($controller, UPACallback::PATH_CART);
        $this->assertSame($redirect, $controller->execute());
    }

    /** @covers \Omise\Payment\Controller\Callback\UPACallback */
    public function testUnsupportedOrderStateIsInvalid(): void
    {
        $order = $this->order('complete');
        $order->expects($this->once())->method('addStatusHistoryComment');
        $order->expects($this->once())->method('save');
        $this->session->method('getLastRealOrder')->willReturn($order);
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $controller = $this->getController();
        $redirect = $this->expectRedirect($controller, UPACallback::PATH_CART);
        $this->assertSame($redirect, $controller->execute());
    }

    /** @covers \Omise\Payment\Controller\Callback\UPACallback */
    public function testMissingSessionIdRestoresQuoteAndCancelsOrder(): void
    {
        $payment = $this->createMock(Payment::class);
        $payment->method('getAdditionalInformation')->with('session_id')->willReturn(null);
        $order = $this->order(Order::STATE_PENDING_PAYMENT, $payment);
        $order->expects($this->once())->method('registerCancellation')->willReturnSelf();
        $order->expects($this->once())->method('save');
        $this->session->method('getLastRealOrder')->willReturn($order);
        $this->checkoutSession->expects($this->once())->method('restoreQuote');
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $controller = $this->getController();
        $redirect = $this->expectRedirect($controller, UPACallback::PATH_CART);
        $this->assertSame($redirect, $controller->execute());
    }

    /** @covers \Omise\Payment\Controller\Callback\UPACallback */
    public function testEmptyPaymentsMarksOrderInvalidAndRestoresQuote(): void
    {
        $payment = $this->createMock(Payment::class);
        $payment->method('getAdditionalInformation')->willReturn('session-1');
        $order = $this->order(Order::STATE_PENDING_PAYMENT, $payment);
        $order->expects($this->once())->method('addStatusHistoryComment');
        $order->expects($this->once())->method('save');
        $this->session->method('getLastRealOrder')->willReturn($order);
        $this->omiseCheckoutSession->method('getSessionInfo')->willReturn((object)[
            'status' => 'succeeded', 'payments' => [],
        ]);
        $this->checkoutSession->expects($this->once())->method('restoreQuote');
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $controller = $this->getController();
        $redirect = $this->expectRedirect($controller, UPACallback::PATH_CART);
        $this->assertSame($redirect, $controller->execute());
    }

    /** @covers \Omise\Payment\Controller\Callback\UPACallback */
    public function testSuccessfulChargeRedirectsToSuccess(): void
    {
        $payment = $this->createMock(Payment::class);
        $payment->method('getAdditionalInformation')->willReturn('session-1');
        $order = $this->order(Order::STATE_PENDING_PAYMENT, $payment);
        $this->session->method('getLastRealOrder')->willReturn($order);
        $this->omiseCheckoutSession->method('getSessionInfo')->willReturn((object)[
            'status' => 'succeeded', 'payments' => [
                ['status' => 'pending', 'charge_id' => 'ch_old'],
                ['status' => 'succeeded', 'charge_id' => 'ch_final'],
            ],
        ]);
        $charge = $this->getMockBuilder(Charge::class)->disableOriginalConstructor()
            ->onlyMethods(['isFailed'])->getMock();
        $charge->method('isFailed')->willReturn(false);
        $this->charge->expects($this->once())->method('find')->with('ch_final')->willReturn($charge);
        $controller = $this->getController();
        $redirect = $this->expectRedirect($controller, UPACallback::PATH_SUCCESS);
        $this->assertSame($redirect, $controller->execute());
    }

    /** @covers \Omise\Payment\Controller\Callback\UPACallback */
    public function testChargeLookupFailureCancelsOrderAndReturnsToCart(): void
    {
        $payment = $this->createMock(Payment::class);
        $payment->method('getAdditionalInformation')->willReturn('session-1');
        $order = $this->order(Order::STATE_PENDING_PAYMENT, $payment);
        $order->expects($this->once())->method('registerCancellation')->willReturnSelf();
        $order->expects($this->once())->method('save');
        $this->session->method('getLastRealOrder')->willReturn($order);
        $this->omiseCheckoutSession->method('getSessionInfo')->willReturn((object)[
            'status' => 'succeeded', 'payments' => [['charge_id' => 'ch_1']],
        ]);
        $this->charge->method('find')->willReturn(null);
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $controller = $this->getController();
        $redirect = $this->expectRedirect($controller, UPACallback::PATH_CART);
        $this->assertSame($redirect, $controller->execute());
    }
}
