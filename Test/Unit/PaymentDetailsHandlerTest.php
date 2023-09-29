<?php

namespace Omise\Payment\Test\Unit;

use Omise\Payment\Gateway\Response\PaymentDetailsHandler;
use Omise\Payment\Helper\OmiseHelper;
use PHPUnit\Framework\TestCase;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Mockery as m;

class PaymentDetailsHandlerTest extends TestCase
{

    private $omiseHelperMock;
    private $curlMock;
    private $builderMock;
    private $paymentMock;
    private $infoMock;
    private $chargeMock;
    private $orderMock;
    private $currencyMock;

    protected function setUp(): void
    {
        $this->omiseHelperMock = m::mock(OmiseHelper::class);
        $this->curlMock = m::mock(Curl::class);
        $this->builderMock = m::mock(BuilderInterface::class);
        $this->paymentMock = m::mock(PaymentDataObjectInterface::class);
        $this->infoMock = m::mock(OrderPaymentInterface::class);
        $this->orderMock = m::mock(OrderInterface::class);
        $this->currencyMock = m::mock(stdClass::class);

        $this->chargeMock = m::mock(stdClass::class);
        $this->chargeMock->id = 'charge_xxx';
        $this->chargeMock->authorize_uri = 'https://omise.co/authorized';
        $this->chargeMock->expires_at = '2023-09-29T06:49:35Z';
    }

    /**
     * @covers Omise\Payment\Gateway\Response\PaymentDetailsHandler
     */
    public function testHandler()
    {
        $this->omiseHelperMock->shouldReceive('isPayableByImageCode')->once();
        $this->builderMock->shouldReceive('setOrder')->once()->andReturn($this->builderMock);
        $this->builderMock->shouldReceive('setPayment')->once()->andReturn($this->builderMock);
        $this->builderMock->shouldReceive('setTransactionId')->once()->andReturn($this->builderMock);
        $this->builderMock->shouldReceive('setAdditionalInformation')->once()->andReturn($this->builderMock);
        $this->builderMock->shouldReceive('setFailSafe')->once()->andReturn($this->builderMock);
        $this->builderMock->shouldReceive('build')->once()->andReturn($this->builderMock);

        $this->currencyMock->shouldReceive('formatTxt')->once();

        $this->orderMock->shouldReceive('getBaseCurrency')->once()->andReturn($this->currencyMock);
        $this->orderMock->shouldReceive('getTotalDue')->once()->andReturn(1000);

        $this->infoMock->shouldReceive('getMethod')->once()->andReturn('promptpay');
        $this->infoMock->shouldReceive('getOrder')->once()->andReturn($this->orderMock);
        $this->infoMock->shouldReceive('setAdditionalInformation')->times(4);
        $this->infoMock->shouldReceive('prependMessage')->once();
        $this->infoMock->shouldReceive('addTransactionCommentsToOrder')->once();

        $this->paymentMock->shouldReceive('getPayment')->andReturn($this->infoMock);

        $model = new PaymentDetailsHandler($this->omiseHelperMock, $this->curlMock, $this->builderMock);
        $model->handle(['payment' => $this->paymentMock], [
            'charge' => $this->chargeMock
        ]);
        $this->expectNotToPerformAssertions();
    }
}
