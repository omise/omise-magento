<?php

namespace Omise\Payment\Test\Unit;

use Magento\Sales\Model\Order;
use PHPUnit\Framework\TestCase;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Omise\Payment\Block\Checkout\Onepage\Success\PromptpayAdditionalInformation;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency;
use Mockery as m;

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
        $this->contextMock =  m::mock(Context::class)->makePartial();
        $this->checkoutSessionMock =  m::mock(Session::class);
        $this->orderMock =  m::mock(Order::class);
        $this->eventManagerMock =  m::mock(ManagerInterface::class);
        $this->scopeConfigMock =  m::mock(ScopeConfigInterface::class);
        $this->currencyMock =  m::mock(Currency::class)->makePartial();
        $this->paymentMock =  m::mock();
    }

    /**
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\PromptpayAdditionalInformation
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\AdditionalInformation
     */
    public function testPromptpayAdditionalInformation()
    {
        $this->paymentMock->shouldReceive('getData')->andReturn([
            "amount_ordered" => 1000,
            "additional_information" => [
                "charge_expires_at" => "2023-09-29T06:49:35Z",
                "payment_type" => "promptpay"
            ]
        ]);
        $this->eventManagerMock->shouldReceive('dispatch')->times(2);
        $this->scopeConfigMock->shouldReceive('getValue')->once();

        $this->contextMock->shouldReceive('getEventManager')->andReturn($this->eventManagerMock);
        $this->contextMock->shouldReceive('getScopeConfig')->andReturn($this->scopeConfigMock);

        $this->orderMock->shouldReceive('getPayment')->andReturn($this->paymentMock);
        $this->orderMock->shouldReceive('getOrderCurrency')->andReturn($this->currencyMock);
        $this->checkoutSessionMock->shouldReceive('getLastRealOrder')->andReturn($this->orderMock);
        $model = new PromptpayAdditionalInformation($this->contextMock, $this->checkoutSessionMock, []);

        $html = $model->toHtml();
        $this->assertNotNull($html);

        $this->assertEquals("2023-09-29T06:49:35Z", $model->getChargeExpiresAt());
    }
}
