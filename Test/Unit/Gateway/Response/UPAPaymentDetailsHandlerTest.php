<?php

namespace Omise\Payment\Test\Unit\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Omise\Payment\Gateway\Response\UPAPaymentDetailsHandler;
use Omise\Payment\Helper\OmiseHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Omise\Payment\Gateway\Response\UPAPaymentDetailsHandler
 */
class UPAPaymentDetailsHandlerTest extends TestCase
{
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
        $this->helper = $this->createMock(
            OmiseHelper::class
        );

        $this->handler = new UPAPaymentDetailsHandler(
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
        $response = ['session' => $session];
        
        $currency = $this->createMock(
            \Magento\Directory\Model\Currency::class
        );
        $currency->expects($this->once())
            ->method('formatTxt')
            ->with(100)
            ->willReturn('USD 100.00');
        
        $order = $this->createConfiguredMock(Order::class, [
            'getBaseCurrency'    => $currency,
            'getTotalDue'         => 100
        ]);

        $payment = $this->createConfiguredMock(Payment::class, [
            'getOrder' => $order,
            'getMethod' => 'omise_promptpay'
        ]);

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

        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);

        $paymentDO->method('getPayment')
            ->willReturn($payment);

        $handlingSubject = [
            'payment' => $paymentDO
        ];

        $order->expects($this->once())
            ->method('addStatusHistoryComment')
            ->with(
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
