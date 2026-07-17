<?php

namespace Omise\Payment\Test\Unit\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Omise\Payment\Gateway\Response\UPAPaymentDetailsHandler;
use Omise\Payment\Helper\OmiseHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Omise\Payment\Gateway\Response\UPAPaymentDetailsHandler
 */
class UPAPaymentDetailsHandlerTest extends TestCase
{
    /**
     * @var BuilderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transactionBuilder;

    /**
     * @var OmiseHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $helper;

    /**
     * @var UPAPaymentDetailsHandler
     */
    private $handler;

    protected function setUp(): void
    {
        $this->transactionBuilder = $this->createMock(
            BuilderInterface::class
        );

        $this->helper = $this->createMock(
            OmiseHelper::class
        );

        $this->handler = new UPAPaymentDetailsHandler(
            $this->transactionBuilder,
            $this->helper
        );
    }

    /**
     * @covers \Omise\Payment\Gateway\Response\UPAPaymentDetailsHandler::handle
     */
    public function testHandle()
    {
        $sessionId = 'sess_123456';
        $redirectUrl = 'https://checkout.omise.co/redirect';

        $session = new \stdClass();
        $session->object = 'checkout_session';
        $session->id = $sessionId;
        $session->redirect_url = $redirectUrl;

        $response = [
            'session' => $session
        ];

        /**
         * Currency
         */
        $currency = $this->createMock(
            \Magento\Directory\Model\Currency::class
        );

        $currency->expects($this->once())
            ->method('formatTxt')
            ->with(100)
            ->willReturn('USD 100.00');

        /**
         * Order
         */
        $order = $this->createMock(Order::class);

        $order->method('getBaseCurrency')
            ->willReturn($currency);

        $order->method('getTotalDue')
            ->willReturn(100);

        /**
         * Payment
         */
        $payment = $this->createMock(Payment::class);

        $payment->method('getOrder')
            ->willReturn($order);

        $payment->method('getMethod')
            ->willReturn('omise_promptpay');

        $this->helper->expects($this->once())
            ->method('getMethodId')
            ->with('omise_promptpay')
            ->willReturn('promptpay');

        $calls = [];

        $payment->expects($this->exactly(3))
            ->method('setAdditionalInformation')
            ->willReturnCallback(
                function ($key, $value) use (&$calls) {
                    $calls[] = [$key, $value];
                    return $this;
                }
            );

        $payment->expects($this->once())
            ->method('prependMessage')
            ->with($this->isType('object'))
            ->willReturn('Processing amount of USD 100.00 via Omise Checkout Gateway.');

        /**
         * Payment Data Object
         */
        $paymentDO = $this->createMock(
            PaymentDataObjectInterface::class
        );

        $paymentDO->method('getPayment')
            ->willReturn($payment);

        $handlingSubject = [
            'payment' => $paymentDO
        ];

        /**
         * Transaction
         */
        $transaction = $this->createMock(
            Transaction::class
        );

        /**
         * Transaction Builder
         */
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
            ->with($sessionId)
            ->willReturnSelf();

        $this->transactionBuilder->expects($this->once())
            ->method('setAdditionalInformation')
            ->with([
                Transaction::RAW_DETAILS => (array)$payment
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

        /**
         * Verify order comment
         */
        $payment->expects($this->once())
            ->method('addTransactionCommentsToOrder')
            ->with(
                $transaction,
                'Processing amount of USD 100.00 via Omise Checkout Gateway.'
            );

        $this->handler->handle(
            $handlingSubject,
            $response
        );

        $this->assertSame(
            [
                ['upa_redirect_uri', $redirectUrl],
                ['session_id', $sessionId],
                ['payment_type', 'promptpay']
            ],
            $calls
        );
    }
}