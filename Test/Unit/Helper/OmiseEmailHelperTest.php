<?php
declare(strict_types=1);

namespace Omise\Payment\Test\Unit\Helper;

use Omise\Payment\Helper\OmiseEmailHelper;
use Omise\Payment\Model\Config\Cc;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\CacheInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Checkout\Model\Session;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Omise\Payment\Helper\OmiseEmailHelper
 * @covers ::__construct
 */
class OmiseEmailHelperTest extends TestCase
{
    /** @var OmiseEmailHelper */
    private $helper;

    /** @var OrderSender */
    private $orderSender;

    /** @var InvoiceSender */
    private $invoiceSender;

    /** @var Session */
    private $checkoutSession;

    /** @var Cc */
    private $config;

    /** @var CacheInterface */
    private $cache;

    protected function setUp(): void
    {
        $this->orderSender = $this->createMock(OrderSender::class);
        $this->invoiceSender = $this->createMock(InvoiceSender::class);

        // Explicitly allow Magento magic methods
        $this->checkoutSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods([
                'setForceOrderMailSentOnSuccess',
                'setForceInvoiceMailSentOnSuccess'
            ])
            ->getMock();

        $this->config = $this->createMock(Cc::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $context = $this->createMock(Context::class);

        $this->helper = new OmiseEmailHelper(
            $this->orderSender,
            $this->invoiceSender,
            $this->checkoutSession,
            $context,
            $this->config,
            $this->cache
        );
    }

    /**
     * Covers:
     * - sendInvoiceAndConfirmationEmails()
     * - indirect call to sendInvoiceEmail()
     * @covers ::sendInvoiceAndConfirmationEmails
     * @covers ::sendInvoiceEmail
     */
    public function testSendInvoiceAndConfirmationEmailsProcessing(): void
    {
        $order = $this->createMock(Order::class);
        $invoice = $this->createMock(Invoice::class);

        $order->method('getEmailSent')->willReturn(false);
        $order->method('getInvoiceCollection')->willReturn([$invoice]);

        $invoice->method('getId')->willReturn(1);

        $this->config->method('getSendInvoiceAtOrderStatus')
            ->willReturn(Order::STATE_PROCESSING);

        $this->cache->method('load')->willReturn(false);

        $this->checkoutSession->expects($this->once())
            ->method('setForceOrderMailSentOnSuccess')
            ->with(true);

        $this->checkoutSession->expects($this->once())
            ->method('setForceInvoiceMailSentOnSuccess')
            ->with(true);

        $this->orderSender->expects($this->once())
            ->method('send')
            ->with($order, true);

        $this->invoiceSender->expects($this->once())
            ->method('send')
            ->with($invoice, true);

        $this->cache->expects($this->once())
            ->method('save');

        $this->helper->sendInvoiceAndConfirmationEmails($order);
    }

    /**
     * @covers ::sendInvoiceAndConfirmationEmails
     */
    public function testSendInvoiceAndConfirmationEmailsNonProcessing(): void
    {
        $order = $this->createMock(Order::class);

        $order->method('getEmailSent')->willReturn(false);

        $this->config->method('getSendInvoiceAtOrderStatus')
            ->willReturn('pending');

        $this->orderSender->expects($this->once())->method('send');
        $this->invoiceSender->expects($this->never())->method('send');

        $this->helper->sendInvoiceAndConfirmationEmails($order);
    }

    /**
     * @covers ::sendInvoiceAndConfirmationEmails
     */
    public function testSendInvoiceAndConfirmationEmailsAlreadySent(): void
    {
        $order = $this->createMock(Order::class);

        $order->method('getEmailSent')->willReturn(true);

        $this->orderSender->expects($this->never())->method('send');
        $this->invoiceSender->expects($this->never())->method('send');

        $this->helper->sendInvoiceAndConfirmationEmails($order);
    }

    /**
     * @covers ::sendInvoiceEmail
     */
    public function testSendInvoiceEmailCacheHit(): void
    {
        $order = $this->createMock(Order::class);
        $invoice = $this->createMock(Invoice::class);

        $invoice->method('getId')->willReturn(99);
        $order->method('getInvoiceCollection')->willReturn([$invoice]);

        $this->cache->method('load')->willReturn('sent');

        $this->invoiceSender->expects($this->never())->method('send');

        $this->helper->sendInvoiceEmail($order);
    }
}
