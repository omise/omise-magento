<?php

namespace Omise\Payment\Test\Unit;

use PHPUnit\Framework\TestCase;
use Omise\Payment\Model\Config\Atome;
use Omise\Payment\Test\Mock\InfoMock;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Omise\Payment\Gateway\Validator\APMRequestValidator;

class APMRequestValidatorTest extends TestCase
{
    private $infoMock;

    private $orderMock;

    private $paymentDataObject;

    private $model;

    protected function setUp(): void
    {
        $this->orderMock = $this->getMockBuilder(OrderAdapterInterface::class)
            ->getMockForAbstractClass();

        $this->infoMock = $this->getMockBuilder(InfoMock::class)->getMock();

        $this->infoMock->method('getMethod')->willReturn(Atome::CODE);

        $this->paymentDataObject = new PaymentDataObject(
            $this->orderMock,
            $this->infoMock
        );

        $this->model = new APMRequestValidator();
    }

    /**
     * @covers Omise\Payment\Gateway\Validator\APMRequestValidator
     */
    public function testCurrencyNotSupported()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Currency not supported');
        $this->orderMock->method('getCurrencyCode')->willReturn("USD");
        $this->orderMock->method('getGrandTotalAmount')->willReturn(100);
        $this->model->build([
            'payment' => $this->paymentDataObject,
        ]);
    }

    /**
     * @covers Omise\Payment\Gateway\Validator\APMRequestValidator
     */
    public function testMinAmountShouldThrowError()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Amount must be greater than 20.00 THB');
        $this->orderMock->method('getCurrencyCode')->willReturn("THB");
        $this->orderMock->method('getGrandTotalAmount')->willReturn(10);
        $this->model->build([
            'payment' => $this->paymentDataObject,
        ]);
    }

    /**
     * @covers Omise\Payment\Gateway\Validator\APMRequestValidator
     */
    public function testValidAmountShouldNotThrowError()
    {
        $this->expectNotToPerformAssertions();
        $this->orderMock->method('getCurrencyCode')->willReturn("THB");
        $this->orderMock->method('getGrandTotalAmount')->willReturn(20);
        $this->model->build([
            'payment' => $this->paymentDataObject,
        ]);
    }

    /**
     * @covers Omise\Payment\Gateway\Validator\APMRequestValidator
     */
    public function testMaxAmountShouldThrowError()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Amount must be less than 150,000.00 THB');
        $this->orderMock->method('getCurrencyCode')->willReturn("THB");
        $this->orderMock->method('getGrandTotalAmount')->willReturn(200000);
        $this->model->build([
            'payment' => $this->paymentDataObject,
        ]);
    }
}
