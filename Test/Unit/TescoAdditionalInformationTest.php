<?php

namespace Omise\Payment\Test\Unit;

use Magento\Checkout\Model\Session;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Omise\Payment\Block\Checkout\Onepage\Success\TescoAdditionalInformation;
use PHPUnit\Framework\TestCase;

class TescoAdditionalInformationTest extends TestCase
{
    private $contextMock;
    private $checkoutSessionMock;
    private $orderMock;
    private $paymentMock;
    private $eventManagerMock;
    private $scopeConfigMock;
    private $currencyMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->checkoutSessionMock = $this->createMock(Session::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->currencyMock = $this->createMock(Currency::class);

        $this->contextMock->method('getEventManager')->willReturn($this->eventManagerMock);
        $this->contextMock->method('getScopeConfig')->willReturn($this->scopeConfigMock);
        $this->eventManagerMock->method('dispatch');
        $this->orderMock->method('getPayment')->willReturn($this->paymentMock);
        $this->checkoutSessionMock->method('getLastRealOrder')->willReturn($this->orderMock);
    }

    /**
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\TescoAdditionalInformation
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\AdditionalInformation
     */
    public function testRendersTescoAdditionalInformation(): void
    {
        $this->paymentMock->method('getData')->willReturn([
            'amount_ordered' => 1000,
            'additional_information' => [
                'payment_type' => 'bill_payment_tesco_lotus',
                'barcode' => '1234567890'
            ]
        ]);
        $this->orderMock->method('getOrderCurrency')->willReturn($this->currencyMock);
        $this->currencyMock->method('getCurrencyCode')->willReturn('THB');

        $model = new TescoAdditionalInformation($this->contextMock, $this->checkoutSessionMock, []);
        $model->toHtml();

        $this->assertSame('1,000.00 THB', $model->getData('order_amount'));
        $this->assertSame('1234567890', $model->getData('offline_code'));
    }

    /**
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\TescoAdditionalInformation
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\AdditionalInformation
     */
    public function testDoesNotRenderForUpaPayment(): void
    {
        $this->paymentMock->method('getData')->willReturn([
            'additional_information' => [
                'payment_type' => 'bill_payment_tesco_lotus',
                'session_id' => 'session_123'
            ]
        ]);

        $model = new TescoAdditionalInformation($this->contextMock, $this->checkoutSessionMock, []);

        $this->assertEmpty($model->toHtml());
    }

    /**
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\TescoAdditionalInformation
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\AdditionalInformation
     */
    public function testDoesNotRenderForDifferentPaymentType(): void
    {
        $this->paymentMock->method('getData')->willReturn([
            'additional_information' => [
                'payment_type' => 'promptpay'
            ]
        ]);

        $model = new TescoAdditionalInformation($this->contextMock, $this->checkoutSessionMock, []);

        $this->assertEmpty($model->toHtml());
    }
}
