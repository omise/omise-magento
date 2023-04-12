<?php

namespace Omise\Payment\Test\Unit;

use PHPUnit\Framework\TestCase;
use Omise\Payment\Model\Config\Atome;
use Omise\Payment\Test\Mock\InfoMock;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Omise\Payment\Gateway\Validator\APMRequestValidator;

class APMRequestValidatorTest extends TestCase
{
    private $infoMock;

    private $orderMock;

    private $addressMock;

    private $paymentDataObject;

    private $model;

    protected function setUp(): void
    {
        $this->addressMock = $this->getMockBuilder(AddressInterface::class)->getMock();
        $this->addressMock->method('getCountryId')->willReturn('TH');

        $this->orderMock = $this->getMockBuilder(OrderAdapterInterface::class)
            ->getMockForAbstractClass();
        $this->orderMock->method('getShippingAddress')->willReturn($this->addressMock);

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
     * @covers Omise\Payment\Helper\PhoneNumberFormatter
     */
    public function testCurrencyNotSupported()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Currency not supported');
        $this->orderMock->method('getCurrencyCode')->willReturn("USD");
        $this->orderMock->method('getGrandTotalAmount')->willReturn(100);
        $this->infoMock->method('getAdditionalInformation')->willReturn('0987654321');
        $this->model->build([
            'payment' => $this->paymentDataObject,
        ]);
    }

    /**
     * @covers Omise\Payment\Gateway\Validator\APMRequestValidator
     * @covers Omise\Payment\Helper\PhoneNumberFormatter
     */
    public function testMinAmountShouldThrowError()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Amount must be greater than 20.00 THB');
        $this->orderMock->method('getCurrencyCode')->willReturn("THB");
        $this->orderMock->method('getGrandTotalAmount')->willReturn(10);
        $this->infoMock->method('getAdditionalInformation')->willReturn('0987654321');
        $this->model->build([
            'payment' => $this->paymentDataObject,
        ]);
    }

    /**
     * @covers Omise\Payment\Gateway\Validator\APMRequestValidator
     * @covers Omise\Payment\Helper\PhoneNumberFormatter
     */
    public function testValidAmountShouldNotThrowError()
    {
        $this->expectNotToPerformAssertions();
        $this->orderMock->method('getCurrencyCode')->willReturn("THB");
        $this->orderMock->method('getGrandTotalAmount')->willReturn(20);
        $this->infoMock->method('getAdditionalInformation')->willReturn('0987654321');
        $this->model->build([
            'payment' => $this->paymentDataObject,
        ]);
    }

    /**
     * @covers Omise\Payment\Gateway\Validator\APMRequestValidator
     * @covers Omise\Payment\Helper\PhoneNumberFormatter
     */
    public function testMaxAmountShouldThrowError()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Amount must be less than 150,000.00 THB');
        $this->orderMock->method('getCurrencyCode')->willReturn("THB");
        $this->orderMock->method('getGrandTotalAmount')->willReturn(200000);
        $this->infoMock->method('getAdditionalInformation')->willReturn('0987654321');
        $this->model->build([
            'payment' => $this->paymentDataObject,
        ]);
    }

    /**
     * @covers Omise\Payment\Gateway\Validator\APMRequestValidator
     * @covers Omise\Payment\Helper\PhoneNumberFormatter
     */
    public function testInvalidAtomePhoneNumberValidation()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Phone number should be a number in Atome');
        $this->infoMock->method('getAdditionalInformation')->willReturn('0987');
        $this->orderMock->method('getCurrencyCode')->willReturn("THB");
        $this->orderMock->method('getGrandTotalAmount')->willReturn(100);
        $this->model->build([
            'payment' => $this->paymentDataObject,
        ]);
    }

    /**
     * @covers Omise\Payment\Gateway\Validator\APMRequestValidator
     * @covers Omise\Payment\Helper\PhoneNumberFormatter
     */
    public function testValidAtomePhoneNumberValidation()
    {
        $this->expectNotToPerformAssertions();
        $this->infoMock->method('getAdditionalInformation')->willReturn('+66987654321');
        $this->orderMock->method('getCurrencyCode')->willReturn("THB");
        $this->orderMock->method('getGrandTotalAmount')->willReturn(100);
        $this->model->build([
            'payment' => $this->paymentDataObject,
        ]);
    }
}
