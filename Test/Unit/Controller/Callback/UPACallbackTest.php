<?php

namespace Omise\Payment\Test\Unit\Controller\Callback;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Omise\Payment\Controller\Callback\UPACallback;
use Omise\Payment\Helper\OmiseEmailHelper;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Model\Api\Charge;
use Omise\Payment\Model\Api\CheckoutSession;
use Omise\Payment\Model\Config\Cc;
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
    private $charge;
    private $helper;
    private $emailHelper;
    private $config;
    private $checkoutSession;
    private $request;
    private $omiseCheckoutSession;
    private $transactionBuilder;
    private $messageManager;

    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);

        $this->session = $this->createMock(Session::class);
        $this->omise = $this->createMock(Omise::class);
        $this->charge = $this->createMock(Charge::class);
        $this->helper = $this->createMock(OmiseHelper::class);
        $this->emailHelper = $this->createMock(OmiseEmailHelper::class);
        $this->config = $this->createMock(Cc::class);
        $this->request = $this->createMock(Http::class);
        
        $this->checkoutSession = $this->createMock(
            \Magento\Checkout\Model\Session::class
        );

        $this->omiseCheckoutSession = $this->createMock(
            \Omise\Payment\Model\Api\CheckoutSession::class
        );
        $this->transactionBuilder = $this->createMock(BuilderInterface::class);

        $this->messageManager = $this->createMock(ManagerInterface::class);

        $this->context->method('getMessageManager')
            ->willReturn($this->messageManager);

        $this->omise->method('defineUserAgent');
        $this->omise->method('defineApiVersion');
        $this->omise->method('defineApiKeys');
    }

    private function getController()
    {
        $controller = $this->getMockBuilder(UPACallback::class)
            ->setConstructorArgs(
                [
                $this->context,
                $this->session,
                $this->omise,
                $this->charge,
                $this->helper,
                $this->emailHelper,
                $this->config,
                $this->checkoutSession,
                $this->request,
                $this->omiseCheckoutSession,
                $this->transactionBuilder
                ]
            )
            ->onlyMethods(['_redirect', 'getRequest'])
            ->getMock();

        $controller->method('getRequest')
            ->willReturn($this->request);

        return $controller;
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\UPACallback
     */
    public function testExecuteWithMissingPayment()
    {
        $order = $this->createMock(Order::class);

        $this->session->method('getLastRealOrder')
            ->willReturn($order);

        $order->method('getId')
            ->willReturn(1);

        $order->method('getPayment')
            ->willReturn(null);

        $controller = $this->getController();

        $controller->expects($this->once())
            ->method('_redirect')
            ->with('checkout/cart', ['_secure' => true]);

        $controller->execute();
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\UPACallback
     */
    public function testExecuteWithInvalidPaymentsArray()
    {
        $order = $this->createMock(Order::class);
        $payment = $this->createMock(Payment::class);

        $this->session->method('getLastRealOrder')
            ->willReturn($order);

        $order->method('getId')->willReturn(1);
        $order->method('getPayment')->willReturn($payment);

        $payment->method('getAdditionalInformation')
            ->with('session_id')
            ->willReturn('session_123');

        $order->method('getState')
            ->willReturn(Order::STATE_PENDING_PAYMENT);

        $sessionInfo = new \stdClass();
        $sessionInfo->payments = null;

        $this->omiseCheckoutSession->method('getSessionInfo')
            ->willReturn($sessionInfo);

        $controller = $this->getController();

        $controller->expects($this->once())
            ->method('_redirect');

        $controller->execute();
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\UPACallback
     */
    public function testExecuteWithInvalidPaymentsData()
    {
        $order = $this->createMock(Order::class);
        $payment = $this->createMock(Payment::class);

        $this->session->method('getLastRealOrder')
            ->willReturn($order);

        $order->method('getId')->willReturn(1);
        $order->method('getPayment')->willReturn($payment);

        $payment->method('getAdditionalInformation')
            ->with('session_id')
            ->willReturn('session_123');

        $order->method('getState')
            ->willReturn(Order::STATE_PENDING_PAYMENT);

        $sessionInfo = new \stdClass();
        
        $sessionInfo->payments = [
            ['charge_id' => 'chrg_test_123']
        ];
        $this->omiseCheckoutSession->method('getSessionInfo')
            ->willReturn($sessionInfo);

        $controller = $this->getController();

        $controller->expects($this->once())
            ->method('_redirect');

        $controller->execute();
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\UPACallback
     */
    public function testExecuteWithInvalidOrderState()
    {
        $order = $this->createMock(Order::class);
        $payment = $this->createMock(Payment::class);

        $this->session->method('getLastRealOrder')
            ->willReturn($order);

        $order->method('getId')->willReturn(1);
        $order->method('getPayment')->willReturn($payment);

        $payment->method('getAdditionalInformation')
            ->willReturn('session_123');

        $order->method('getState')
            ->willReturn(Order::STATE_COMPLETE);

        $sessionInfo = new \stdClass();
        $sessionInfo->payments = [
            ['charge_id' => 'chrg_test']
        ];

        $this->request->method('getParam')
            ->willReturn('chrg_test');

        $this->omiseCheckoutSession->method('getSessionInfo')
            ->willReturn($sessionInfo);

        $controller = $this->getController();

        $controller->expects($this->once())
            ->method('_redirect');

        $controller->execute();
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\UPACallback
     */
    public function testExecuteWithoutSessionId()
    {
        $order = $this->createMock(Order::class);
        $payment = $this->createMock(Payment::class);

        $this->session->method('getLastRealOrder')
            ->willReturn($order);

        $order->method('getId')->willReturn(1);
        $order->method('getPayment')->willReturn($payment);

        $order->method('getState')
            ->willReturn(Order::STATE_PENDING_PAYMENT);

        $payment->method('getAdditionalInformation')
            ->willReturn('');

        $sessionInfo = new \stdClass();
        $sessionInfo->payments = [
            ['charge_id' => 'chrg_test']
        ];

        $this->request->method('getParam')
            ->willReturn('chrg_test');

        $this->omiseCheckoutSession->method('getSessionInfo')
            ->willReturn($sessionInfo);

        $order->expects($this->once())
        ->method('hasInvoices')
        ->willReturn(false);

        $order->expects($this->once())
            ->method('registerCancellation')
            ->willReturnSelf();

        $order->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage');

        $this->session->expects($this->once())
            ->method('restoreQuote');

        $controller = $this->getController();

        $controller->expects($this->once())
            ->method('_redirect')
            ->with('checkout/cart', ['_secure' => true]);

        $controller->execute();
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\UPACallback
     */
    public function testExecuteWithChargeFailure()
    {
        $order = $this->createMock(Order::class);
        $payment = $this->createMock(Payment::class);

        $this->session->method('getLastRealOrder')
            ->willReturn($order);

        $order->method('getId')
            ->willReturn(1);

        $order->method('getPayment')
            ->willReturn($payment);

        $order->method('getState')
            ->willReturn(Order::STATE_PENDING_PAYMENT);

        $payment->method('getAdditionalInformation')
            ->with('session_id')
            ->willReturn('session_123');

        $this->request->method('getParam')
            ->with('chargeId')
            ->willReturn('chrg_test');

        $sessionInfo = new \stdClass();
        $sessionInfo->payments = [
            ['charge_id' => 'chrg_test']
        ];

        $this->omiseCheckoutSession
            ->method('getSessionInfo')
            ->with('session_123')
            ->willReturn($sessionInfo);

        $charge = $this->getMockBuilder(
            \Omise\Payment\Model\Api\BaseObject::class
        )
            ->disableOriginalConstructor()
            ->addMethods(['isFailed', 'isSuccessful'])
            ->getMock();

        $charge->id = 'chrg_test';
        $charge->failure_message = 'bank rejected';

        $charge->method('isFailed')
            ->willReturn(true);

        $this->charge->expects($this->once())
            ->method('find')
            ->with('chrg_test')
            ->willReturn($charge);

        $this->checkoutSession->expects($this->once())
            ->method('restoreQuote');

        $order->expects($this->once())
            ->method('hasInvoices')
            ->willReturn(false);

        $order->expects($this->once())
            ->method('registerCancellation')
            ->willReturnSelf();

        $order->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage');

        $controller = $this->getController();

        $controller->expects($this->once())
            ->method('_redirect')
            ->with('checkout/cart', ['_secure' => true]);

        $controller->execute();
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\UPACallback
     */
    public function testExecuteWithSuccessfulCharge()
    {
        $order = $this->createMock(Order::class);
        $payment = $this->createMock(Payment::class);

        $invoice = $this->getMockBuilder(
            \Magento\Sales\Model\Order\Invoice::class
        )
        ->disableOriginalConstructor()
        ->getMock();

        $transaction = $this->createMock(Transaction::class);

        $orderConfig = $this->createMock(
            \Magento\Sales\Model\Order\Config::class
        );

        $currency = $this->createMock(
            \Magento\Directory\Model\Currency::class
        );

        $this->session->method('getLastRealOrder')
            ->willReturn($order);

        $order->method('getId')
            ->willReturn(1);

        $order->method('getPayment')
            ->willReturn($payment);

        $order->method('getState')
            ->willReturn(Order::STATE_PENDING_PAYMENT);

        $payment->method('getAdditionalInformation')
            ->with('session_id')
            ->willReturn('session_123');

        $payment->method('getMethod')
            ->willReturn('omise_promptpay');

        $this->request->method('getParam')
            ->with('chargeId')
            ->willReturn('chrg_test');

        $sessionInfo = new \stdClass();
        $sessionInfo->payments = [
            ['charge_id' => 'chrg_test']
        ];


        $this->omiseCheckoutSession
            ->method('getSessionInfo')
            ->willReturn($sessionInfo);

        $charge = $this->getMockBuilder(
            \Omise\Payment\Model\Api\BaseObject::class
        )
            ->disableOriginalConstructor()
            ->addMethods(['isFailed', 'isSuccessful'])
            ->getMock();

        $charge->id = 'chrg_test';

        $charge->method('isFailed')
            ->willReturn(false);

        $charge->method('isSuccessful')
            ->willReturn(true);
        
        $charge->capture = true;

        $this->charge->expects($this->once())
            ->method('find')
            ->with('chrg_test')
            ->willReturn($charge);

        $payment->expects($this->once())
            ->method('setTransactionId')
            ->with('chrg_test')
            ->willReturnSelf();

        $payment->expects($this->once())
            ->method('setLastTransId')
            ->with('chrg_test')
            ->willReturnSelf();

        $this->config->method('isWebhookEnabled')
            ->willReturn(false);

        $order->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_PROCESSING)
            ->willReturnSelf();

        $order->method('getConfig')
            ->willReturn($orderConfig);

        $orderConfig->method('getStateDefaultStatus')
            ->with(Order::STATE_PROCESSING)
            ->willReturn('processing');

        $order->expects($this->once())
            ->method('setStatus')
            ->with('processing')
            ->willReturnSelf();

        $this->helper->expects($this->once())
            ->method('createInvoiceAndMarkAsPaid')
            ->with($order, 'chrg_test')
            ->willReturn($invoice);

        $this->emailHelper->expects($this->once())
            ->method('sendInvoiceAndConfirmationEmails')
            ->with($order);

        $this->helper->expects($this->once())
            ->method('getOmiseLabelByOmiseCode')
            ->with('omise_promptpay')
            ->willReturn('PromptPay');

        $invoice->method('getBaseGrandTotal')
            ->willReturn(100);

        $order->method('getBaseCurrency')
            ->willReturn($currency);

        $currency->method('formatTxt')
            ->with(100)
            ->willReturn('100.00');

        $payment->expects($this->once())
            ->method('addTransaction')
            ->with(Transaction::TYPE_PAYMENT,$invoice,$this->anything())
            ->willReturn($transaction);

        $payment->expects($this->once())
        ->method('addTransactionCommentsToOrder')
        ->with(
            $transaction,
            $this->anything()
        )
        ->willReturnSelf();

        $order->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $controller = $this->getController();

        $redirectResult = $this->createMock(Redirect::class);

        $controller->expects($this->once())
            ->method('_redirect')
            ->with(
                'checkout/onepage/success',
                ['_secure' => true]
            )
            ->willReturn($redirectResult);

        $result = $controller->execute();
        $this->assertSame($redirectResult, $result);
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\UPACallback
     */
    public function testExecuteWithPendingCharge()
    {
        $order = $this->createMock(Order::class);
        $payment = $this->createMock(Payment::class);
        $transaction = $this->createMock(Transaction::class);

        $orderConfig = $this->createMock(
            \Magento\Sales\Model\Order\Config::class
        );

        $this->session->method('getLastRealOrder')
            ->willReturn($order);

        $order->method('getId')
            ->willReturn(1);

        $order->method('getPayment')
            ->willReturn($payment);

        $order->method('getState')
            ->willReturn(Order::STATE_PENDING_PAYMENT);

        $payment->method('getAdditionalInformation')
            ->willReturn('session_123');

        $this->request->method('getParam')
            ->with('chargeId')
            ->willReturn('chrg_test');

        $sessionInfo = new \stdClass();
        $sessionInfo->payments = [
            ['charge_id' => 'chrg_test']
        ];

        $this->omiseCheckoutSession
            ->method('getSessionInfo')
            ->willReturn($sessionInfo);

        $charge = $this->getMockBuilder(
            \Omise\Payment\Model\Api\BaseObject::class
        )
            ->disableOriginalConstructor()
            ->addMethods(['isFailed', 'isSuccessful'])
            ->getMock();

        $charge->id = 'chrg_test';

        $charge->method('isFailed')
            ->willReturn(false);

        $charge->method('isSuccessful')
            ->willReturn(false);

        $this->charge->expects($this->once())
            ->method('find')
            ->with('chrg_test')
            ->willReturn($charge);

        $payment->expects($this->once())
            ->method('setTransactionId')
            ->with('chrg_test')
            ->willReturnSelf();

        $payment->expects($this->once())
            ->method('setLastTransId')
            ->with('chrg_test')
            ->willReturnSelf();

        $this->config->method('isWebhookEnabled')
            ->willReturn(false);

        // handlePending assertions
        $order->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_PAYMENT_REVIEW)
            ->willReturnSelf();

        $order->method('getConfig')
            ->willReturn($orderConfig);

        $orderConfig->expects($this->once())
            ->method('getStateDefaultStatus')
            ->with(Order::STATE_PAYMENT_REVIEW)
            ->willReturn('payment_review');

        $order->expects($this->once())
            ->method('setStatus')
            ->with('payment_review')
            ->willReturnSelf();

        $payment->expects($this->once())
            ->method('addTransaction')
            ->with(Transaction::TYPE_PAYMENT)
            ->willReturn($transaction);

        $transaction->expects($this->once())
            ->method('setIsClosed')
            ->with(false)
            ->willReturnSelf();

        $payment->expects($this->once())
            ->method('addTransactionCommentsToOrder')
            ->with(
                $transaction,
                $this->anything()
            )
            ->willReturnSelf();

        $order->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $controller = $this->getController();

        $redirectResult = $this->createMock(Redirect::class);

        $controller->expects($this->once())
            ->method('_redirect')
            ->with(
                'checkout/onepage/success',
                ['_secure' => true]
            )
            ->willReturn($redirectResult);

        $result = $controller->execute();
        $this->assertSame($redirectResult, $result);
    }
    
    /**
     * @covers \Omise\Payment\Controller\Callback\UPACallback
     */
    public function testExecuteInvalidOrderRedirectsToCart()
    {
        $order = $this->createMock(Order::class);

        $this->session->method('getLastRealOrder')
            ->willReturn($order);

        $order->method('getId')
            ->willReturn(null);

        $this->request->method('getParam')
            ->with('chargeId')
            ->willReturn('chrg_test');
        
        $controller = $this->getController();

        $controller->expects($this->once())
            ->method('_redirect')
            ->with('checkout/cart', ['_secure' => true])
            ->willReturn($this->createMock(Redirect::class));

        $controller->execute();
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\UPACallback
     */
    public function testExecuteProcessingOrderRedirectsToSuccess()
    {
        $order = $this->createMock(Order::class);
        $payment = $this->createMock(Payment::class);

        $this->session->method('getLastRealOrder')
            ->willReturn($order);

        $this->request->method('getParam')
            ->willReturn('chrg_test');

        $order->method('getId')
            ->willReturn(1);

        $order->method('getPayment')
            ->willReturn($payment);

        $payment->method('getAdditionalInformation')
            ->willReturn('session_123');

        $sessionInfo = new \stdClass();
        $sessionInfo->payments = [
            ['charge_id' => 'chrg_test']
        ];

        $this->omiseCheckoutSession
            ->method('getSessionInfo')
            ->willReturn($sessionInfo);

        $order->expects($this->exactly(2))
            ->method('getState')
            ->willReturn(Order::STATE_PROCESSING);

        $controller = $this->getController();

        $controller->expects($this->once())
            ->method('_redirect')
            ->with(
                'checkout/onepage/success',
                ['_secure' => true]
            )
            ->willReturn($this->createMock(Redirect::class));

        $controller->execute();
    }
    
    /**
     * @covers \Omise\Payment\Controller\Callback\UPACallback
     */
    public function testExecuteChargeFindThrowsException()
    {
        $order = $this->createMock(Order::class);
        $payment = $this->createMock(Payment::class);

        $this->session->method('getLastRealOrder')
            ->willReturn($order);

        $this->request->method('getParam')
            ->willReturn('chrg_test');

        $order->method('getId')
            ->willReturn(1);

        $order->method('getPayment')
            ->willReturn($payment);

        $order->method('getState')
            ->willReturn(Order::STATE_PENDING_PAYMENT);

        $payment->method('getAdditionalInformation')
            ->willReturn('session_123');

        $sessionInfo = new \stdClass();
        $sessionInfo->payments = [
            ['charge_id' => 'chrg_test']
        ];

        $this->omiseCheckoutSession
            ->method('getSessionInfo')
            ->willReturn($sessionInfo);

        $this->charge->expects($this->once())
            ->method('find')
            ->willThrowException(new \Exception('API Error'));

        $order->expects($this->once())
            ->method('hasInvoices')
            ->willReturn(false);

        $order->expects($this->once())
            ->method('registerCancellation')
            ->with('API Error')
            ->willReturnSelf();

        $order->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage');

        $controller = $this->getController();

        $controller->expects($this->once())
            ->method('_redirect')
            ->with('checkout/cart', ['_secure' => true])
            ->willReturn($this->createMock(Redirect::class));

        $controller->execute();
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\UPACallback
     * @uses \Omise\Payment\Model\Api\Error
    */
    public function testExecuteChargeErrorRestoresQuoteAndRedirectsToCart()
    {
        $order = $this->createMock(Order::class);
        $payment = $this->createMock(Payment::class);

        $this->session->method('getLastRealOrder')
            ->willReturn($order);

        $this->request->method('getParam')
            ->with('chargeId')
            ->willReturn('chrg_test');

        $order->method('getId')
            ->willReturn(1);

        $order->method('getPayment')
            ->willReturn($payment);

        $order->method('getState')
            ->willReturn(Order::STATE_PENDING_PAYMENT);

        $payment->method('getAdditionalInformation')
            ->with('session_id')
            ->willReturn('session_123');

        $sessionInfo = new \stdClass();
        $sessionInfo->payments = [
            ['charge_id' => 'chrg_test']
        ];

        $this->omiseCheckoutSession
            ->method('getSessionInfo')
            ->willReturn($sessionInfo);

        $this->checkoutSession->expects($this->once())
            ->method('restoreQuote');

        $this->charge->method('find')
            ->with('chrg_test')
            ->willReturn(new \Omise\Payment\Model\Api\Error(['message' => 'Failed message']));

        $order->expects($this->once())
            ->method('hasInvoices')
            ->willReturn(false);

        $order->expects($this->once())
            ->method('registerCancellation')
            ->with('Failed message')
            ->willReturnSelf();

        $order->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage');

        $controller = $this->getController();

        $controller->expects($this->once())
            ->method('_redirect')
            ->with('checkout/cart', ['_secure' => true])
            ->willReturn($this->createMock(Redirect::class));

        $controller->execute();
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\UPACallback
     */
    public function testExecuteWithWebhookEnabledBuildsTransactionAndRedirectsToSuccess()
    {
        $order = $this->createMock(Order::class);
        $payment = $this->createMock(Payment::class);
        $transaction = $this->createMock(Transaction::class);

        $this->session->method('getLastRealOrder')
            ->willReturn($order);

        $this->request->method('getParam')
            ->with('chargeId')
            ->willReturn('chrg_test');

        $order->method('getId')
            ->willReturn(1);

        $order->method('getPayment')
            ->willReturn($payment);

        $order->method('getState')
            ->willReturn(Order::STATE_PENDING_PAYMENT);

        $payment->method('getAdditionalInformation')
            ->with('session_id')
            ->willReturn('session_123');

        $sessionInfo = new \stdClass();
        $sessionInfo->payments = [
            ['charge_id' => 'chrg_test']
        ];

        $this->omiseCheckoutSession
            ->method('getSessionInfo')
            ->willReturn($sessionInfo);

        $charge = $this->getMockBuilder(
            \Omise\Payment\Model\Api\BaseObject::class
        )
        ->disableOriginalConstructor()
        ->addMethods(['isFailed', 'isSuccessful'])
        ->getMock();

        $charge->id = 'chrg_test';
        $charge->status = 'pending';

        $charge->method('isFailed')
            ->willReturn(false);

        $charge->method('isSuccessful')
            ->willReturn(false);

        $this->charge->method('find')
            ->with('chrg_test')
            ->willReturn($charge);

        $this->config->method('isWebhookEnabled')
            ->willReturn(true);

        $this->transactionBuilder->expects($this->once())
            ->method('setPayment')
            ->with($payment)
            ->willReturnSelf();

        $this->transactionBuilder->expects($this->once())
            ->method('setOrder')
            ->with($order)
            ->willReturnSelf();

        $this->transactionBuilder->expects($this->once())
            ->method('setTransactionId')
            ->with('chrg_test')
            ->willReturnSelf();

        $this->transactionBuilder->expects($this->once())
            ->method('setAdditionalInformation')
            ->with([
                Transaction::RAW_DETAILS => [
                    'omise_charge_id' => 'chrg_test',
                    'status' => 'pending'
                ]
            ])
            ->willReturnSelf();

        $this->transactionBuilder->expects($this->once())
            ->method('setFailSafe')
            ->with(true)
            ->willReturnSelf();

        $this->transactionBuilder->expects($this->once())
            ->method('build')
            ->with(Transaction::TYPE_PAYMENT)
            ->willReturn($transaction);

        $order->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $controller = $this->getController();

        $redirectResult = $this->createMock(Redirect::class);

        $controller->expects($this->once())
            ->method('_redirect')
            ->with('checkout/onepage/success', ['_secure' => true])
            ->willReturn($redirectResult);

        $result = $controller->execute();

        $this->assertInstanceOf(Redirect::class, $result);
        $this->assertSame($redirectResult, $result);
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\UPACallback
     */
    public function testExecuteWithSuccessfulAuthorizedCharge()
    {
        $order = $this->createMock(Order::class);
        $payment = $this->createMock(Payment::class);
        $transaction = $this->createMock(Transaction::class);

        $orderConfig = $this->createMock(
            \Magento\Sales\Model\Order\Config::class
        );

        $currency = $this->createMock(
            \Magento\Directory\Model\Currency::class
        );

        $this->session->method('getLastRealOrder')
            ->willReturn($order);

        $order->method('getId')
            ->willReturn(1);

        $order->method('getPayment')
            ->willReturn($payment);

        $order->method('getState')
            ->willReturn(Order::STATE_PENDING_PAYMENT);

        $payment->method('getAdditionalInformation')
            ->with('session_id')
            ->willReturn('session_123');

        $payment->method('getMethod')
            ->willReturn('omise_creditcard');

        $this->request->method('getParam')
            ->with('chargeId')
            ->willReturn('chrg_test');

        $sessionInfo = new \stdClass();
        $sessionInfo->payments = [
            ['charge_id' => 'chrg_test']
        ];

        $this->omiseCheckoutSession
            ->method('getSessionInfo')
            ->willReturn($sessionInfo);

        $charge = $this->getMockBuilder(
            \Omise\Payment\Model\Api\BaseObject::class
        )
            ->disableOriginalConstructor()
            ->addMethods(['isFailed', 'isSuccessful'])
            ->getMock();

        $charge->id = 'chrg_test';
        $charge->capture = false;

        $charge->method('isFailed')
            ->willReturn(false);

        $charge->method('isSuccessful')
            ->willReturn(true);

        $this->charge->method('find')
            ->willReturn($charge);

        $payment->expects($this->once())
            ->method('setTransactionId')
            ->with('chrg_test')
            ->willReturnSelf();

        $payment->expects($this->once())
            ->method('setLastTransId')
            ->with('chrg_test')
            ->willReturnSelf();

        $this->config->method('isWebhookEnabled')
            ->willReturn(false);

        $order->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_PROCESSING)
            ->willReturnSelf();

        $order->method('getConfig')
            ->willReturn($orderConfig);

        $orderConfig->method('getStateDefaultStatus')
            ->willReturn('processing');

        $order->expects($this->once())
            ->method('setStatus')
            ->with('processing')
            ->willReturnSelf();

        $this->helper->expects($this->once())
            ->method('createInvoiceAndMarkAsPaid')
            ->with($order, 'chrg_test', false);

        $this->emailHelper->expects($this->once())
            ->method('sendInvoiceAndConfirmationEmails')
            ->with($order);

        $this->helper->method('getOmiseLabelByOmiseCode')
            ->willReturn('Credit Card');

        $order->method('getBaseCurrency')
            ->willReturn($currency);

        $order->method('getTotalDue')
            ->willReturn(100);

        $currency->method('formatTxt')
            ->willReturn('100.00');

        $payment->expects($this->once())
            ->method('prependMessage')
            ->willReturn('Authorized message');

        $payment->expects($this->once())
            ->method('addTransaction')
            ->with(Transaction::TYPE_AUTH)
            ->willReturn($transaction);

        $payment->expects($this->once())
            ->method('addTransactionCommentsToOrder')
            ->with(
                $transaction,
                'Authorized message'
            )
            ->willReturnSelf();

        $order->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $controller = $this->getController();

        $redirectResult = $this->createMock(Redirect::class);

        $controller->expects($this->once())
            ->method('_redirect')
            ->with(
                'checkout/onepage/success',
                ['_secure' => true]
            )
            ->willReturn($redirectResult);

        $this->assertSame($redirectResult, $controller->execute());
    }
}
