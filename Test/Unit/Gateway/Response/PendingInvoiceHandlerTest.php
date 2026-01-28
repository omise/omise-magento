<?php

namespace Omise\Payment\Test\Unit\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Omise\Payment\Gateway\Response\PendingInvoiceHandler;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Helper\OmiseEmailHelper;
use Omise\Payment\Model\Config\Cc as Config;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Omise\Payment\Gateway\Response\PendingInvoiceHandler
 */
class PendingInvoiceHandlerTest extends TestCase
{
   /** @var PendingInvoiceHandler */
    private $handler;

    /** @var OmiseHelper */
    private $helper;

    /** @var OmiseEmailHelper */
    private $emailHelper;

    /** @var Config */
    private $config;

    protected function setUp(): void
    {
        $this->helper = $this->createMock(OmiseHelper::class);
        $this->emailHelper = $this->createMock(OmiseEmailHelper::class);
        $this->config = $this->createMock(Config::class);

        $this->handler = new PendingInvoiceHandler(
            $this->helper,
            $this->emailHelper,
            $this->config
        );
    }

    /**
     * @covers ::__construct
     * @covers ::handle
     */
    public function testEarlyReturnIfNot3DSecureAndPaymentActionNotAuthorizeCapture(): void
    {
        $handlingSubject = ['paymentAction' => 'some_other_action'];
        $response = ['charge' => []];

        $this->helper->method('is3DSecureEnabled')->willReturn(false);

        $this->assertNull($this->handler->handle($handlingSubject, $response));
    }

    /**
     * @covers ::__construct
     * @covers ::handle
     */
    public function testEarlyReturnIfSendInvoiceAtProcessing(): void
    {
        $handlingSubject = ['paymentAction' => PendingInvoiceHandler::ACTION_AUTHORIZE_CAPTURE];
        $response = ['charge' => []];

        $this->helper->method('is3DSecureEnabled')->willReturn(true);
        $this->config->method('getSendInvoiceAtOrderStatus')
            ->willReturn(PendingInvoiceHandler::STATE_PROCESSING);

        $this->assertNull($this->handler->handle($handlingSubject, $response));
    }

    /**
     * @covers ::__construct
     * @covers ::handle
     */
    public function testNormalPathCreatesInvoiceAndSendsEmail(): void
    {
        $handlingSubject = ['paymentAction' => PendingInvoiceHandler::ACTION_AUTHORIZE_CAPTURE];
        $response = ['charge' => []];

        $this->helper->method('is3DSecureEnabled')->willReturn(true);
        $this->config->method('getSendInvoiceAtOrderStatus')->willReturn('any_other_status');

        // Mock payment / order / invoice
        $paymentMock = $this->createMock(\Magento\Sales\Model\Order\Payment::class);
        $orderMock = $this->createMock(Order::class);
        $invoiceMock = $this->createMock(Invoice::class);

        $paymentMock->method('getOrder')->willReturn($orderMock); // <-- allow multiple calls

        $orderMock->expects($this->once())->method('prepareInvoice')->willReturn($invoiceMock);
        $invoiceMock->expects($this->once())->method('register');
        $orderMock->expects($this->once())
            ->method('addRelatedObject')
            ->with($invoiceMock)
            ->willReturnSelf();
        $orderMock->expects($this->once())->method('save');

        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $paymentDO->method('getPayment')->willReturn($paymentMock);
        $handlingSubject['payment'] = $paymentDO;

        $this->emailHelper->expects($this->once())
            ->method('sendInvoiceEmail')
            ->with($orderMock);

        $this->handler->handle($handlingSubject, $response);
    }
}
