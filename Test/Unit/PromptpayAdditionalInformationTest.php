<?php

namespace Omise\Payment\Test\Unit;

use Magento\Sales\Model\Order;
use PHPUnit\Framework\TestCase;
use Magento\Checkout\Model\Session;
use Omise\Payment\Test\Mock\PaymentMock;
use Magento\Framework\View\Element\Template\Context;
use Omise\Payment\Block\Checkout\Onepage\Success\PromptpayAdditionalInformation;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency;

class PromptpayAdditionalInformationTest extends TestCase
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
        $this->contextMock = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $this->checkoutSessionMock = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();
        $this->orderMock = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $this->paymentMock = $this->getMockBuilder(PaymentMock::class)->getMock();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)->getMock();
        $this->currencyMock = $this->getMockBuilder(Currency::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\PromptpayAdditionalInformation
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\AdditionalInformation
     */
    public function testChargeExpiryDate()
    {
        $this->paymentMock->method('getData')->willReturn([
            "amount_ordered" => 1000,
            "additional_information" => [
                "charge_expires_at" => "2023-09-29T06:49:35Z",
                "payment_type" => "promptpay"
            ]
        ]);
        $this->contextMock->method('getEventManager')->willReturn($this->eventManagerMock);
        $this->contextMock->method('getScopeConfig')->willReturn($this->scopeConfigMock);
        $this->orderMock->method('getPayment')->willReturn($this->paymentMock);
        $this->orderMock->method('getOrderCurrency')->willReturn($this->currencyMock);
        $this->checkoutSessionMock->method('getLastRealOrder')->willReturn($this->orderMock);
        $model = new PromptpayAdditionalInformation($this->contextMock, $this->checkoutSessionMock, []);

        $this->assertEquals("Sep 29, 2023 01:49 PM", $model->getChargeExpiryDate());

        $html = $model->toHtml();
        $this->assertNotNull($html);
    }
}
