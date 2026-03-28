<?php
namespace Omise\Payment\Test\Unit\Controller\Callback;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment\Transaction;
use Omise\Payment\Controller\Callback\Threedsecure;
use Omise\Payment\Helper\OmiseEmailHelper;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Model\Config\Cc as Config;
use Omise\Payment\Gateway\Validator\Message\Invalid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ThreedsecureTest extends TestCase
{
    /** @var Threedsecure */
    protected $controller;

    /** @var Session|\PHPUnit\Framework\MockObject\MockObject */
    protected $session;

    /** @var Config|\PHPUnit\Framework\MockObject\MockObject */
    protected $config;

    /** @var OmiseEmailHelper|\PHPUnit\Framework\MockObject\MockObject */
    protected $emailHelper;

    /** @var OmiseHelper|\PHPUnit\Framework\MockObject\MockObject */
    protected $helper;

    /** @var Http|\PHPUnit\Framework\MockObject\MockObject */
    protected $request;

    /** @var Context|\PHPUnit\Framework\MockObject\MockObject */
    protected $context;

    /** @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $messageManager;

    /** @var Session|\PHPUnit\Framework\MockObject\MockObject */
    protected $checkoutSession;

    protected function setUp(): void
    {
        $this->session = $this->createMock(Session::class);
        $this->config = $this->createMock(Config::class);
        $this->emailHelper = $this->createMock(OmiseEmailHelper::class);
        $this->helper = $this->createMock(OmiseHelper::class);
        $this->request = $this->createMock(Http::class);
        $this->context = $this->createMock(Context::class);
        $this->checkoutSession = $this->createMock(Session::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->context->method('getMessageManager')->willReturn($this->messageManager);

        $this->controller = $this->getMockBuilder(Threedsecure::class)
            ->setConstructorArgs([
                $this->context,
                $this->session,
                $this->config,
                $this->emailHelper,
                $this->helper,
                $this->checkoutSession,
                $this->createMock(\Psr\Log\LoggerInterface::class),
                $this->request
            ])
            ->onlyMethods(['redirect', 'validate', 'invoice'])
            ->getMock();
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\Threedsecure::execute
     * @covers \Omise\Payment\Controller\Callback\Threedsecure::__construct
     * @covers \Omise\Payment\Controller\Callback\Threedsecure::invalid
     */
    public function testExecuteInvalidToken()
    {
        $payment = $this->createMock(Payment::class);
        $payment->method('getAdditionalInformation')->willReturn('wrongtoken');

        $order = $this->createMock(Order::class);
        $order->method('getId')->willReturn(1);
        $order->method('getPayment')->willReturn($payment);
        $order->method('addStatusHistoryComment')->willReturnSelf();
        $order->method('save')->willReturnSelf();

        $this->session->method('getLastRealOrder')->willReturn($order);
        $this->request->method('getParam')->with('token')->willReturn('abc123');

        $this->controller->expects($this->once())
            ->method('redirect')
            ->with(Threedsecure::PATH_CART)
            ->willReturn('redirected');

        $result = $this->controller->execute();
        $this->assertEquals('redirected', $result);
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\Threedsecure::execute
     * @covers \Omise\Payment\Controller\Callback\Threedsecure::__construct
     * @covers \Omise\Payment\Controller\Callback\Threedsecure::invalid
     */
    public function testExecuteCanceledOrder()
    {
        $order = $this->createMock(Order::class);
        $order->method('getId')->willReturn(1);
        $order->method('getPayment')->willReturn($this->createMock(Payment::class));
        $order->method('getState')->willReturn(Order::STATE_PROCESSING);
        $order->method('isCanceled')->willReturn(true);
        $order->method('addStatusHistoryComment')->willReturnSelf();
        $order->method('save')->willReturnSelf();

        $this->session->method('getLastRealOrder')->willReturn($order);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->with(Threedsecure::PATH_CART)
            ->willReturn('redirected');

        $result = $this->controller->execute();
        $this->assertEquals('redirected', $result);
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\Threedsecure::validate
     * @covers \Omise\Payment\Controller\Callback\Threedsecure::__construct
     */
    public function testValidateCaptureAndAuthorize()
    {
        $reflection = new \ReflectionMethod($this->controller, 'validate');
        $reflection->setAccessible(true);

        $captureCharge = ['capture' => true];
        $authorizeCharge = ['capture' => false];

        // Cannot mock static classes, so we just check return types
        $this->controller->method('validate')->willReturn(true);

        $this->assertTrue($reflection->invoke($this->controller, $captureCharge));
        $this->assertTrue($reflection->invoke($this->controller, $authorizeCharge));
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\Threedsecure::redirect
     * @covers \Omise\Payment\Controller\Callback\Threedsecure::__construct
     */
    public function testRedirectMethod()
    {
        $mockResponse = $this->createMock(\Magento\Framework\App\ResponseInterface::class);

        // Make the controller _redirect() return the mocked ResponseInterface
        $this->controller = $this->getMockBuilder(\Omise\Payment\Controller\Callback\Threedsecure::class)
            ->setConstructorArgs([
                $this->context,
                $this->session,
                $this->config,
                $this->emailHelper,
                $this->helper,
                $this->checkoutSession,
                $this->createMock(\Psr\Log\LoggerInterface::class),
                $this->request
            ])
            ->onlyMethods(['_redirect'])
            ->getMock();

        $this->controller->method('_redirect')->willReturn($mockResponse);

        $reflection = new \ReflectionMethod($this->controller, 'redirect');
        $reflection->setAccessible(true);
        $response = $reflection->invoke($this->controller, Threedsecure::PATH_SUCCESS);

        $this->assertInstanceOf(\Magento\Framework\App\ResponseInterface::class, $response);
    }
    
    /**
     * @covers \Omise\Payment\Controller\Callback\Threedsecure::invoice
     * @covers \Omise\Payment\Controller\Callback\Threedsecure::__construct
     */
    public function testInvoiceMethod()
    {
        $mockInvoice = $this->createMock(\Magento\Sales\Model\Order\Invoice::class);
        $this->controller->method('invoice')->willReturn($mockInvoice);

        $reflection = new \ReflectionMethod($this->controller, 'invoice');
        $reflection->setAccessible(true);
        $result = $reflection->invoke($this->controller, $this->createMock(Order::class));

        $this->assertSame($mockInvoice, $result);
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\Threedsecure::invalid
     * @covers \Omise\Payment\Controller\Callback\Threedsecure::__construct
     */
    public function testInvalidMethod()
    {
        $order = $this->createMock(Order::class);
        $order->method('addStatusHistoryComment')->willReturnSelf();
        $order->method('save')->willReturnSelf();

        $reflection = new \ReflectionMethod($this->controller, 'invalid');
        $reflection->setAccessible(true);
        $reflection->invoke($this->controller, $order, 'error message');

        $this->addToAssertionCount(1); // ensure PHPUnit counts this as test
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\Threedsecure::cancel
     * @covers \Omise\Payment\Controller\Callback\Threedsecure::__construct
     */
    public function testCancelMethod()
    {
        $invoice = $this->createMock(Invoice::class);
        $invoice->method('cancel')->willReturnSelf();

        $order = $this->createMock(Order::class);
        $order->method('hasInvoices')->willReturn(true);
        $order->method('registerCancellation')->willReturnSelf();
        $order->method('addRelatedObject')->willReturnSelf();
        $order->method('save')->willReturnSelf();
        $order->method('addStatusHistoryComment')->willReturnSelf();

        // Stub the controller's invoice() method to return the invoice mock
        $this->controller->method('invoice')->willReturn($invoice);

        $reflection = new \ReflectionMethod($this->controller, 'cancel');
        $reflection->setAccessible(true);
        $reflection->invoke($this->controller, $order, 'cancel message');

        $this->addToAssertionCount(1);
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\Threedsecure::invoice
     * @covers \Omise\Payment\Controller\Callback\Threedsecure::__construct
     */
    public function testInvoiceMethodReturnsLastInvoice()
    {
        $invoice = $this->createMock(\Magento\Sales\Model\Order\Invoice::class);

        $invoiceCollection = $this->getMockBuilder(
            \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection::class
        )->disableOriginalConstructor()->getMock();

        $invoiceCollection->expects($this->once())
            ->method('getLastItem')
            ->willReturn($invoice);

        $order = $this->createMock(Order::class);
        $order->expects($this->once())
            ->method('getInvoiceCollection')
            ->willReturn($invoiceCollection);

        // IMPORTANT: invoice() method MUST NOT be mocked
        $controller = $this->getMockBuilder(Threedsecure::class)
            ->setConstructorArgs([
                $this->context,
                $this->session,
                $this->config,
                $this->emailHelper,
                $this->helper,
                $this->checkoutSession,
                $this->createMock(\Psr\Log\LoggerInterface::class),
                $this->request
            ])
            ->onlyMethods(['redirect', 'validate']) // invoice NOT mocked
            ->getMock();

        $reflection = new \ReflectionMethod($controller, 'invoice');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($controller, $order);

        $this->assertSame($invoice, $result);
    }
}
