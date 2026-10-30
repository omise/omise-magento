<?php

namespace Omise\Payment\Test\Unit;

use Magento\Checkout\Model\Session;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Omise\Payment\Block\Checkout\Onepage\Success\ConveniencestoreAdditionalInformation;
use PHPUnit\Framework\TestCase;

class ConveniencestoreAdditionalInformationTest extends TestCase
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
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\ConveniencestoreAdditionalInformation
     */
    public function testRendersConveniencestoreInformation(): void
    {
        $this->paymentMock->method('getData')->willReturn([
            'amount_ordered' => 1000,
            'additional_information' => [
                'payment_type' => 'econtext',
                'charge_authorize_uri' => 'https://example.com/pay'
            ]
        ]);
        $this->orderMock->method('getOrderCurrency')->willReturn($this->currencyMock);
        $this->currencyMock->method('getCurrencyCode')->willReturn('THB');

        $model = new ConveniencestoreAdditionalInformation($this->contextMock, $this->checkoutSessionMock, []);
        $model->toHtml();

        $this->assertSame('https://example.com/pay', $model->getData('link'));
        $this->assertSame('1,000.00 THB', $model->getData('order_amount'));
    }

    /**
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\ConveniencestoreAdditionalInformation
     */
    public function testDoesNotRenderForUpaPayment(): void
    {
        $this->paymentMock->method('getData')->willReturn([
            'additional_information' => [
                'session_id' => 'session_123',
                'payment_type' => 'econtext'
            ]
        ]);

        $model = new ConveniencestoreAdditionalInformation($this->contextMock, $this->checkoutSessionMock, []);

        $this->assertEmpty($model->toHtml());
    }

    /**
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\ConveniencestoreAdditionalInformation
     */
    public function testDoesNotRenderWhenPaymentTypeIsMissing(): void
    {
        $this->paymentMock->method('getData')->willReturn([
            'additional_information' => []
        ]);

        $model = new ConveniencestoreAdditionalInformation($this->contextMock, $this->checkoutSessionMock, []);

        $this->assertEmpty($model->toHtml());
    }

    /**
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\ConveniencestoreAdditionalInformation
     */
    public function testDoesNotRenderForDifferentPaymentType(): void
    {
        $this->paymentMock->method('getData')->willReturn([
            'additional_information' => [
                'payment_type' => 'promptpay'
            ]
        ]);

        $model = new ConveniencestoreAdditionalInformation($this->contextMock, $this->checkoutSessionMock, []);

        $this->assertEmpty($model->toHtml());
    }
}
