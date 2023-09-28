<?php

namespace Omise\Payment\Test\Unit;

use Magento\Sales\Model\Order;
use PHPUnit\Framework\TestCase;
use Magento\Checkout\Model\Session;
use Omise\Payment\Test\Mock\PaymentMock;
use Magento\Framework\View\Element\Template\Context;
use Omise\Payment\Block\Checkout\Onepage\Success\PromptpayAdditionalInformation;

class PromptpayAdditionalInformationTest extends TestCase
{
    private $contextMock;
    private $checkoutSessionMock;
    private $orderMock;
    private $paymentMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $this->checkoutSessionMock = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();
        $this->orderMock = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->getMock();
        $this->paymentMock = $this->getMockBuilder(PaymentMock::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * test expiry date
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\PromptpayAdditionalInformation
     * @covers Omise\Payment\Block\Checkout\Onepage\Success\AdditionalInformation
     */
    public function testChargeExpiryDate()
    {
        $this->paymentMock->method('getData')->willReturn([
            "additional_information" => [
                "charge_expires_at" => "2023-09-29T06:49:35Z"
            ]
        ]);
        $this->orderMock->method('getPayment')->willReturn($this->paymentMock);
        $this->checkoutSessionMock->method('getLastRealOrder')->willReturn($this->orderMock);
        $model = new PromptpayAdditionalInformation($this->contextMock, $this->checkoutSessionMock, []);

        $this->assertEquals("Sep 29, 2023 01:49 PM", $model->getChargeExpiryDate());
    }
}
