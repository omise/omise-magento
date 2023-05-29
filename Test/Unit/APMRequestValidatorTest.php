<?php

namespace Omise\Payment\Test\Unit;

use PHPUnit\Framework\TestCase;
use Omise\Payment\Model\Config\Atome;
use Omise\Payment\Test\Mock\InfoMock;
use Omise\Payment\Test\Mock\OrderMock;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Omise\Payment\Gateway\Validator\APMRequestValidator;

class APMRequestValidatorTest extends TestCase
{
    private $infoMock;

    private $orderMock;

    private $addressMock;

    private $model;

    protected function setUp(): void
    {
        $this->addressMock = $this->getMockBuilder(AddressInterface::class)->getMock();
        $this->addressMock->method('getCountryId')->willReturn('TH');

        $this->orderMock = $this->getMockBuilder(OrderMock::class)->getMock();
        $this->orderMock->method('getShippingAddress')->willReturn($this->addressMock);

        $this->infoMock = $this->getMockBuilder(InfoMock::class)->getMock();
        $this->infoMock->method('getMethod')->willReturn(Atome::CODE);
        $this->infoMock->method('getOrder')->willReturn($this->orderMock);

        $this->model = new APMRequestValidator();
    }

    /**
     * @covers Omise\Payment\Gateway\Validator\APMRequestValidator
     */
    public function testComplimentaryProducts()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Complimentary products cannot be billed');
        $this->orderMock->method('getSubTotal')->willReturn(0.0);
        $this->orderMock->method('getCurrencyCode')->willReturn("THB");
        $this->infoMock->method('getAdditionalInformation')->willReturn('0987654321');
        $this->model->build([
            'payment' => new PaymentDataObject(
                $this->orderMock,
                $this->infoMock
            ),
        ]);
    }

    /**
     * @covers Omise\Payment\Gateway\Validator\APMRequestValidator
     */
    public function testCurrencyNotSupported()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Currency not supported');
        $this->orderMock->method('getCurrencyCode')->willReturn("USD");
        $this->infoMock->method('getAdditionalInformation')->willReturn('0987654321');
        $this->model->build([
            'payment' => new PaymentDataObject(
                $this->orderMock,
                $this->infoMock
            ),
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
        $this->infoMock->method('getAdditionalInformation')->willReturn('0987654321');
        $this->model->build([
            'payment' => new PaymentDataObject(
                $this->orderMock,
                $this->infoMock
            ),
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
        $this->infoMock->method('getAdditionalInformation')->willReturn('0987654321');
        $this->model->build([
            'payment' => new PaymentDataObject(
                $this->orderMock,
                $this->infoMock
            ),
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
        $this->infoMock->method('getAdditionalInformation')->willReturn('0987654321');
        $this->model->build([
            'payment' => new PaymentDataObject(
                $this->orderMock,
                $this->infoMock
            ),
        ]);
    }

    /**
     * @covers Omise\Payment\Gateway\Validator\APMRequestValidator
     */
    public function testInvalidAtomePhoneNumberValidation()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Phone number should be a number in Atome');
        $this->infoMock->method('getAdditionalInformation')->willReturn('0987');
        $this->orderMock->method('getCurrencyCode')->willReturn("THB");
        $this->orderMock->method('getGrandTotalAmount')->willReturn(100);
        $this->model->build([
            'payment' => new PaymentDataObject(
                $this->orderMock,
                $this->infoMock
            ),
        ]);
    }

    /**
     * @covers Omise\Payment\Gateway\Validator\APMRequestValidator
     */
    public function testValidAtomePhoneNumberValidation()
    {
        $this->expectNotToPerformAssertions();
        $this->infoMock->method('getAdditionalInformation')->willReturn('+66987654321');
        $this->orderMock->method('getCurrencyCode')->willReturn("THB");
        $this->orderMock->method('getGrandTotalAmount')->willReturn(100);
        $this->model->build([
            'payment' => new PaymentDataObject(
                $this->orderMock,
                $this->infoMock
            ),
        ]);
    }
}
