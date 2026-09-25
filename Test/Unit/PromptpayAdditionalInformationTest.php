<?php

namespace Omise\Payment\Test\Unit;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Request\Http;
use Omise\Payment\Block\Checkout\Onepage\Success\PromptpayAdditionalInformation;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Directory\Model\Currency;

class PromptpayAdditionalInformationTest extends TestCase
{
    private $contextMock;
    private $checkoutSessionMock;
    private $orderMock;
    private $paymentMock;
    private $eventManagerMock;
    private $scopeConfigMock;
    private $currencyMock;
    private $requestMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->checkoutSessionMock = $this->createMock(Session::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->currencyMock = $this->createMock(Currency::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->requestMock = $this->createMock(Http::class);
    }

    /**
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\PromptpayAdditionalInformation
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\AdditionalInformation
     */
    public function testPromptpayAdditionalInformation()
    {
        $this->paymentMock->expects($this->once())
            ->method('getData')
            ->willReturn([
            "amount_ordered" => 1000,
            "additional_information" => [
                "charge_expires_at" => "2023-09-29T06:49:35Z",
                "payment_type" => "promptpay"
            ]
        ]);
        $this->eventManagerMock->expects($this->exactly(2))
            ->method('dispatch');
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue');

        $this->contextMock->method('getEventManager')->willReturn($this->eventManagerMock);
        $this->contextMock->method('getScopeConfig')->willReturn($this->scopeConfigMock);
        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->requestMock->method('getParam')->with('upa')->willReturn(null);

        $this->orderMock->method('getPayment')->willReturn($this->paymentMock);
        $this->orderMock->method('getOrderCurrency')->willReturn($this->currencyMock);
        $this->currencyMock->method('getCurrencyCode')->willReturn('THB');
        $this->checkoutSessionMock->method('getLastRealOrder')->willReturn($this->orderMock);
        $model = new PromptpayAdditionalInformation($this->contextMock, $this->checkoutSessionMock, []);

        $html = $model->toHtml();
        $this->assertNotNull($html);

        $this->assertEquals("2023-09-29T06:49:35Z", $model->getChargeExpiresAt());
    }
}
