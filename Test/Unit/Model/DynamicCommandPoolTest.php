<?php

namespace Omise\Payment\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Omise\Payment\Helper\OmiseHelper;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use Omise\Payment\Model\Config\Installment;
use Omise\Payment\Model\DynamicCommandPool;
use Omise\Payment\Observer\InstallmentDataAssignObserver;

class DynamicCommandPoolTest extends TestCase
{
    private $apmPool;
    private $upaPool;
    private $omiseHelper;
    private $checkoutSession;
    private $model;

    protected function setUp(): void
    {
        $this->apmPool = $this->createMock(CommandPoolInterface::class);
        $this->upaPool = $this->createMock(CommandPoolInterface::class);
        $this->omiseHelper = $this->createMock(OmiseHelper::class);
        $this->checkoutSession = $this->createMock(Session::class);

        $this->model = new DynamicCommandPool(
            $this->apmPool,
            $this->upaPool,
            $this->omiseHelper,
            $this->checkoutSession
        );
    }

    /**
     * @covers Omise\Payment\Model\DynamicCommandPool
     */
    public function testConstructor(): void
    {
        $this->assertInstanceOf(
            DynamicCommandPool::class,
            $this->model
        );
    }

    /**
     * @covers Omise\Payment\Model\DynamicCommandPool
     */
    public function testGetReturnsUpaPoolForInstallmentWithoutWlb(): void
    {
        $command = new \stdClass();

        $payment = $this->createMock(Payment::class);
        $payment->method('getMethod')
            ->willReturn(Installment::CODE);

        $payment->method('getAdditionalInformation')
            ->with(InstallmentDataAssignObserver::WLB)
            ->willReturn(0);

        $quote = $this->createMock(Quote::class);
        $quote->method('getPayment')
            ->willReturn($payment);

        $this->checkoutSession->method('getQuote')
            ->willReturn($quote);

        $this->omiseHelper->method('isAllowUpa')
            ->with(Installment::CODE)
            ->willReturn(true);

        $this->upaPool->expects($this->once())
            ->method('get')
            ->with('authorize')
            ->willReturn($command);

        $this->assertSame(
            $command,
            $this->model->get('authorize')
        );
    }

    /**
     * @covers Omise\Payment\Model\DynamicCommandPool
     */
    public function testGetReturnsApmPoolForInstallmentWithWlb(): void
    {
        $command = new \stdClass();

        $payment = $this->createMock(Payment::class);
        $payment->method('getMethod')
            ->willReturn(Installment::CODE);

        $payment->method('getAdditionalInformation')
            ->with(InstallmentDataAssignObserver::WLB)
            ->willReturn(1);

        $quote = $this->createMock(Quote::class);
        $quote->method('getPayment')
            ->willReturn($payment);

        $this->checkoutSession->method('getQuote')
            ->willReturn($quote);

        $this->omiseHelper->method('isAllowUpa')
            ->with(Installment::CODE)
            ->willReturn(true);

        $this->apmPool->expects($this->once())
            ->method('get')
            ->with('authorize')
            ->willReturn($command);

        $this->assertSame(
            $command,
            $this->model->get('authorize')
        );
    }

    /**
     * @covers Omise\Payment\Model\DynamicCommandPool
     */
    public function testGetReturnsApmPoolForInstallmentWhenUpaNotAllowed(): void
    {
        $command = new \stdClass();

        $payment = $this->createMock(Payment::class);
        $payment->method('getMethod')
            ->willReturn(Installment::CODE);

        $payment->method('getAdditionalInformation')
            ->with(InstallmentDataAssignObserver::WLB)
            ->willReturn(0);

        $quote = $this->createMock(Quote::class);
        $quote->method('getPayment')
            ->willReturn($payment);

        $this->checkoutSession->method('getQuote')
            ->willReturn($quote);

        $this->omiseHelper->method('isAllowUpa')
            ->with(Installment::CODE)
            ->willReturn(false);

        $this->apmPool->expects($this->once())
            ->method('get')
            ->with('authorize')
            ->willReturn($command);

        $this->assertSame(
            $command,
            $this->model->get('authorize')
        );
    }

    /**
     * @covers Omise\Payment\Model\DynamicCommandPool
     */
    public function testGetReturnsUpaPoolForNonInstallmentMethod(): void
    {
        $command = new \stdClass();

        $payment = $this->createMock(Payment::class);
        $payment->method('getMethod')
            ->willReturn('promptpay');

        $quote = $this->createMock(Quote::class);
        $quote->method('getPayment')
            ->willReturn($payment);

        $this->checkoutSession->method('getQuote')
            ->willReturn($quote);

        $this->omiseHelper->method('isAllowUpa')
            ->with('promptpay')
            ->willReturn(true);

        $this->upaPool->expects($this->once())
            ->method('get')
            ->with('authorize')
            ->willReturn($command);

        $this->assertSame(
            $command,
            $this->model->get('authorize')
        );
    }

    /**
     * @covers Omise\Payment\Model\DynamicCommandPool
     */
    public function testGetReturnsApmPoolForNonInstallmentMethodWhenUpaNotAllowed(): void
    {
        $command = new \stdClass();

        $payment = $this->createMock(Payment::class);
        $payment->method('getMethod')
            ->willReturn('promptpay');

        $quote = $this->createMock(Quote::class);
        $quote->method('getPayment')
            ->willReturn($payment);

        $this->checkoutSession->method('getQuote')
            ->willReturn($quote);

        $this->omiseHelper->method('isAllowUpa')
            ->with('promptpay')
            ->willReturn(false);

        $this->apmPool->expects($this->once())
            ->method('get')
            ->with('authorize')
            ->willReturn($command);

        $this->assertSame(
            $command,
            $this->model->get('authorize')
        );
    }
    /**
     * @covers Omise\Payment\Model\DynamicCommandPool
     */
    public function testGetReturnsApmPoolWhenQuoteIsNull(): void
    {
        $command = new \stdClass();

        $this->checkoutSession->method('getQuote')
            ->willReturn(null);

        $this->apmPool->expects($this->once())
            ->method('get')
            ->with('authorize')
            ->willReturn($command);

        $this->assertSame(
            $command,
            $this->model->get('authorize')
        );
    }
    /**
     * @covers Omise\Payment\Model\DynamicCommandPool
     */
    public function testGetReturnsApmPoolWhenPaymentIsNull(): void
    {
        $command = new \stdClass();

        $quote = $this->createMock(Quote::class);
        $quote->method('getPayment')
            ->willReturn(null);

        $this->checkoutSession->method('getQuote')
            ->willReturn($quote);

        $this->apmPool->expects($this->once())
            ->method('get')
            ->with('authorize')
            ->willReturn($command);

        $this->assertSame(
            $command,
            $this->model->get('authorize')
        );
    }
}
