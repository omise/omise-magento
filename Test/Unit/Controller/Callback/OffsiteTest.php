<?php
namespace Omise\Payment\Test\Unit\Controller\Callback;

use PHPUnit\Framework\TestCase;
use Omise\Payment\Controller\Callback\Offsite;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Omise\Payment\Model\Omise;
use Omise\Payment\Model\Api\Charge;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Helper\OmiseEmailHelper;
use Omise\Payment\Model\Config\Cc;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\ResponseInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * @coversDefaultClass \Omise\Payment\Controller\Callback\Offsite
 */
class OffsiteTest extends TestCase
{
    /** @var Offsite */
    private $controller;

    /** @var Session */
    private $session;

    /** @var Charge */
    private $charge;

    /** @var OmiseHelper */
    private $helper;

    /** @var OmiseEmailHelper */
    private $emailHelper;

    /** @var Cc */
    private $config;

    /** @var Session */
    private $checkoutSession;

    /** @var Http */
    private $request;

    /** @var ManagerInterface */
    private $messageManager;

    protected function setUp(): void
    {
        $this->session = $this->createMock(Session::class);
        $this->checkoutSession = $this->createMock(Session::class);
        $this->charge = $this->createMock(Charge::class);
        $this->helper = $this->createMock(OmiseHelper::class);
        $this->emailHelper = $this->createMock(OmiseEmailHelper::class);
        $this->config = $this->createMock(Cc::class);
        $this->request = $this->createMock(Http::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);

        $context = $this->createMock(Context::class);
        $context->method('getRequest')->willReturn($this->request);
        $context->method('getMessageManager')->willReturn($this->messageManager);

        $this->controller = $this->getMockBuilder(Offsite::class)
            ->setConstructorArgs([
                $context,
                $this->session,
                $this->createMock(Omise::class),
                $this->charge,
                $this->helper,
                $this->emailHelper,
                $this->config,
                $this->checkoutSession,
                $this->createMock(LoggerInterface::class),
                $this->request
            ])
            ->onlyMethods(['_redirect'])
            ->getMock();

        $this->controller->method('_redirect')
            ->willReturn($this->createMock(ResponseInterface::class));
    }

    /**
     * @covers ::execute
     * @covers ::isValid
     * @covers ::invalid
     * @covers ::redirect
     * @covers ::__construct
     * @covers ::handleSuccess
     */
    public function testExecuteSuccessfulCharge(): void
    {
        $order = $this->mockOrder('charge_123', 'token_123');

        // Mock order config
        $orderConfig = $this->createMock(OrderConfig::class);
        $orderConfig->method('getStateDefaultStatus')->willReturn('processing');
        $order->method('getConfig')->willReturn($orderConfig);

        // Mock currency
        $currency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['formatTxt'])
            ->getMock();
        $currency->method('formatTxt')->willReturn('$100.00');
        $order->method('getBaseCurrency')->willReturn($currency);

        // Mock successful charge
        $charge = $this->createMock(Charge::class);
        $charge->method('isFailed')->willReturn(false);
        $charge->method('isSuccessful')->willReturn(true);
        $charge->id = 'charge_123';

        $this->charge->method('find')->with('charge_123')->willReturn($charge);
        $this->config->method('isWebhookEnabled')->willReturn(false);
        $this->request->method('getParam')->with('token')->willReturn('token_123');

        $this->helper->method('createInvoiceAndMarkAsPaid')
            ->willReturn($this->createMock(\Magento\Sales\Model\Order\Invoice::class));
        $this->helper->method('getOmiseLabelByOmiseCode')->willReturn('Credit Card');

        $this->emailHelper->expects($this->once())
            ->method('sendInvoiceAndConfirmationEmails')
            ->with($order);

        $result = $this->controller->execute();
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * @covers ::cancel
     * @covers ::__construct
     */
    public function testCancelOrder(): void
    {
        $order = $this->createMock(\Magento\Sales\Model\Order::class);

        // Mock order chainable methods
        $order->method('registerCancellation')->willReturnSelf();
        $order->method('save')->willReturnSelf();
        $order->method('addRelatedObject')->willReturnSelf();

        // Mock hasInvoices() = true
        $order->method('hasInvoices')->willReturn(true);

        // Mock invoice returned by $this->invoice($order)
        $invoice = $this->createMock(\Magento\Sales\Model\Order\Invoice::class);
        $invoice->expects($this->once())->method('cancel');

        // Mock controller invoice() method to return our mock invoice
        $controller = $this->getMockBuilder(Offsite::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['invoice'])
            ->getMock();

        $controller->method('invoice')->with($order)->willReturn($invoice);

        // Mock message manager
        $messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with('Payment failed');

        // Inject message manager into controller
        $reflection = new \ReflectionClass($controller);
        $prop = $reflection->getProperty('messageManager');
        $prop->setAccessible(true);
        $prop->setValue($controller, $messageManager);

        // Use reflection to call protected cancel()
        $method = $reflection->getMethod('cancel');
        $method->setAccessible(true);

        // Call cancel
        $method->invoke($controller, $order, 'Payment failed');
    }

    /**
     * @covers ::invalid
     * @covers ::__construct
     */
    public function testInvalid(): void
    {
        $order = $this->createMock(Order::class);
        $order->method('addStatusHistoryComment')->willReturnSelf();
        $order->method('save')->willReturnSelf();

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with('Invalid payment');

        $ref = new \ReflectionClass($this->controller);
        $method = $ref->getMethod('invalid');
        $method->setAccessible(true);
        $method->invoke($this->controller, $order, 'Invalid payment');
    }

    /**
     * @covers ::invoice
     * @covers ::__construct
     */
    public function testInvoice(): void
    {
        // Mock the Order object
        $order = $this->createMock(\Magento\Sales\Model\Order::class);

        // Mock Invoice collection and last item
        $invoice = $this->createMock(\Magento\Sales\Model\Order\Invoice::class);
        $collection = $this->createMock(\Magento\Framework\Data\Collection::class);

        $collection->method('getLastItem')->willReturn($invoice);
        $order->method('getInvoiceCollection')->willReturn($collection);

        // Call protected invoice() using reflection
        $ref = new \ReflectionClass($this->controller);
        $method = $ref->getMethod('invoice');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, $order);

        // Assert we got the same invoice back
    
        $this->assertSame($invoice, $result);
    }

    /**
     * @covers ::handlePending
     * @covers ::__construct
     * @covers ::redirect
     */
    public function testHandlePending(): void
    {
        $order = $this->createMock(Order::class);
        $payment = $this->createMock(Payment::class);
        $transaction = $this->createMock(Transaction::class);
        $orderConfig = $this->createMock(OrderConfig::class);

        // Mock order config
        $orderConfig->method('getStateDefaultStatus')
            ->with(Order::STATE_PAYMENT_REVIEW)
            ->willReturn('payment_review');

        $order->method('getConfig')->willReturn($orderConfig);

        $order->expects($this->once())->method('setState')->with(Order::STATE_PAYMENT_REVIEW);
        $order->expects($this->once())->method('setStatus')->with('payment_review');

        $payment->expects($this->once())
            ->method('addTransaction')
            ->with(Transaction::TYPE_PAYMENT)
            ->willReturn($transaction);

        $transaction->expects($this->once())->method('setIsClosed')->with(false);

        $payment->expects($this->once())
            ->method('addTransactionCommentsToOrder')
            ->with(
                $transaction,
                $this->callback(function ($message) {
                    if ($message instanceof \Magento\Framework\Phrase) {
                        $message = (string)$message;
                    }
                    return mb_stripos($message, 'The payment is under processing') !== false;
                })
            );

        $order->expects($this->once())->method('save');

        // Call private method via Reflection
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('handlePending');
        $method->setAccessible(true);

        // Invoke the method
        $result = $method->invoke($this->controller, $order, $payment);

        // Assert the result is a ResponseInterface (as handlePending returns redirect)
        $this->assertInstanceOf(\Magento\Framework\App\ResponseInterface::class, $result);
    }

    /**
     * @covers ::handleFailure
     * @covers ::__construct
     */
    public function testHandleFailure(): void
    {
        $charge = new \stdClass();
        $charge->failure_message = 'payment declined';

        // Mock restoreQuote
        $this->checkoutSession->expects($this->once())
            ->method('restoreQuote');

        // Access private method using Reflection
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('handleFailure');
        $method->setAccessible(true);

        // Expect LocalizedException
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage(
            'Payment failed. Payment declined, please contact our support if you have any questions.'
        );

        // Call the private method
        $method->invoke($this->controller, $charge);
    }

    /**
     * Helper to mock order with payment
     */
    private function mockOrder(string $chargeId, string $token): Order
    {
        $order = $this->createMock(Order::class);
        $order->method('getId')->willReturn(1);
        $order->method('getState')->willReturn(Order::STATE_PENDING_PAYMENT);

        $payment = $this->createMock(Payment::class);
        $payment->method('getAdditionalInformation')
            ->willReturnCallback(function ($key) use ($chargeId, $token) {
                return match($key) {
                "charge_id" => $chargeId,
                "token"     => $token,
                default     => null,
                };
            });

        $payment->method('getMethod')->willReturn('offsite_method');

        $order->method('getPayment')->willReturn($payment);
        $this->helper->method('isOffsitePaymentMethod')->willReturn(true);
        $this->session->method('getLastRealOrder')->willReturn($order);

        $order->method('save')->willReturnSelf();
        $order->method('addStatusHistoryComment')->willReturnSelf();
        $order->method('registerCancellation')->willReturnSelf();

        return $order;
    }

    /**
     * @covers ::isValid
     * @covers ::__construct
     * @covers \Omise\Payment\Controller\Callback\Offsite::invalid
     */
    public function testIsValidAllBranches(): void
    {
        $ref = new \ReflectionClass($this->controller);
        $method = $ref->getMethod('isValid');
        $method->setAccessible(true);

        // No order ID
        $order = $this->createMock(Order::class);
        $order->method('getId')->willReturn(null);
        $this->assertFalse($method->invoke($this->controller, $order));

        // No payment
        $order = $this->createMock(Order::class);
        $order->method('getId')->willReturn(1);
        $order->method('getPayment')->willReturn(null);
        $this->assertFalse($method->invoke($this->controller, $order));

        /** -----------------------------
         * VALID CASE (true)
         * FRESH CONTROLLER (IMPORTANT)
         * ----------------------------- */

        $context = $this->createMock(Context::class);

        $request = $this->createMock(Http::class);
        $request->method('getParam')->willReturn('token123');

        $messageManager = $this->createMock(ManagerInterface::class);
        $context->method('getRequest')->willReturn($request);
        $context->method('getMessageManager')->willReturn($messageManager);

        $helper = $this->createMock(OmiseHelper::class);
        $helper->method('isOffsitePaymentMethod')->willReturn(true);

        $session = $this->createMock(Session::class);

        $controller = new Offsite(
            $context,
            $session,
            $this->createMock(Omise::class),
            $this->createMock(Charge::class),
            $helper,
            $this->createMock(OmiseEmailHelper::class),
            $this->createMock(Cc::class),
            $this->createMock(CheckoutSession::class),
            $this->createMock(LoggerInterface::class),
            $request
        );

        $ref = new \ReflectionClass($controller);
        $method = $ref->getMethod('isValid');
        $method->setAccessible(true);

        $order = $this->createMock(Order::class);
        $payment = $this->createMock(Payment::class);

        $order->method('getId')->willReturn(1);
        $order->method('getPayment')->willReturn($payment);
        $order->method('getState')->willReturn(Order::STATE_PENDING_PAYMENT);

        $payment->method('getMethod')->willReturn('offsite_method');
        $payment->method('getAdditionalInformation')->willReturnMap([
            ['token', 'token123'],
            ['charge_id', 'ch_123'],
        ]);

        // THIS WILL PASS
        $this->assertTrue($method->invoke($controller, $order));
    }
}
