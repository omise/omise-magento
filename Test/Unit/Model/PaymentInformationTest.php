<?php
namespace Omise\Payment\Test\Unit\Model;

use Omise\Payment\Model\PaymentInformation;
use Omise\Payment\Api\Data\PaymentInterface;
use Omise\Payment\Test\Unit\Model\Stub\PaymentFactoryStub;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\PaymentException;

if (!class_exists(\Omise\Payment\Api\Data\PaymentInterfaceFactory::class)) {
    class_alias(PaymentFactoryStub::class, \Omise\Payment\Api\Data\PaymentInterfaceFactory::class);
}

class PaymentInformationTest extends TestCase
{
    /** @var Session|\PHPUnit\Framework\MockObject\MockObject */
    private $sessionMock;

    /** @var \Omise\Payment\Api\Data\PaymentInterfaceFactory */
    private $factoryStub;

    /** @var PaymentInformation */
    private $paymentInfo;

    protected function setUp(): void
    {
        $this->sessionMock = $this->createMock(Session::class);

        // Dummy PaymentInterface for factory
        $dataMock = $this->createMock(PaymentInterface::class);

        // Factory stub (via class_alias it satisfies the type hint)
        $this->factoryStub = new \Omise\Payment\Api\Data\PaymentInterfaceFactory($dataMock);

        // Class under test
        $this->paymentInfo = new PaymentInformation($this->sessionMock, $this->factoryStub);
    }

    /**
     * @covers \Omise\Payment\Model\PaymentInformation::__construct
     * @covers \Omise\Payment\Model\PaymentInformation::offsite
     * @covers \Omise\Payment\Model\PaymentInformation::loadOrder
     */
    public function testOffsiteReturnsPaymentData()
    {
        $orderId = 123;

        // Mock order payment
        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->method('getAdditionalInformation')
                    ->with('charge_authorize_uri')
                    ->willReturn('https://authorize.uri');

        // Mock order
        $orderMock = $this->createMock(Order::class);
        $orderMock->method('getId')->willReturn($orderId);
        $orderMock->method('getPayment')->willReturn($paymentMock);

        $this->sessionMock->method('getLastRealOrder')->willReturn($orderMock);

        // Fresh PaymentInterface mock for this test
        $dataMock = $this->createMock(PaymentInterface::class);
        $dataMock->expects($this->once())->method('setOrderId')->with($orderId);
        $dataMock->expects($this->once())->method('setAuthorizeUri')->with('https://authorize.uri');

        // Replace factory stub to return this mock
        $factoryStub = new \Omise\Payment\Api\Data\PaymentInterfaceFactory($dataMock);
        $paymentInfo = new PaymentInformation($this->sessionMock, $factoryStub);

        $result = $paymentInfo->offsite($orderId);

        $this->assertSame($dataMock, $result);
    }

    /**
     * @covers \Omise\Payment\Model\PaymentInformation::__construct
     * @covers \Omise\Payment\Model\PaymentInformation::offsite
     * @covers \Omise\Payment\Model\PaymentInformation::loadOrder
     */
    public function testOffsiteThrowsSessionExceptionIfOrderMissing()
    {
        $orderMock = $this->createMock(Order::class);
        $orderMock->method('getId')->willReturn(null);

        $this->sessionMock->method('getLastRealOrder')->willReturn($orderMock);

        $this->expectException(SessionException::class);
        $this->paymentInfo->offsite(123);
    }

    /**
     * @covers \Omise\Payment\Model\PaymentInformation::__construct
     * @covers \Omise\Payment\Model\PaymentInformation::offsite
     * @covers \Omise\Payment\Model\PaymentInformation::loadOrder
     */
    public function testOffsiteThrowsAuthorizationExceptionIfOrderIdMismatch()
    {
        $orderMock = $this->createMock(Order::class);
        $orderMock->method('getId')->willReturn(999); // Different ID

        $this->sessionMock->method('getLastRealOrder')->willReturn($orderMock);

        $this->expectException(AuthorizationException::class);
        $this->paymentInfo->offsite(123);
    }

    /**
     * @covers \Omise\Payment\Model\PaymentInformation::__construct
     * @covers \Omise\Payment\Model\PaymentInformation::offsite
     * @covers \Omise\Payment\Model\PaymentInformation::loadOrder
     */
    public function testOffsiteThrowsPaymentExceptionIfNoPayment()
    {
        $orderMock = $this->createMock(Order::class);
        $orderMock->method('getId')->willReturn(123);
        $orderMock->method('getPayment')->willReturn(null);

        $this->sessionMock->method('getLastRealOrder')->willReturn($orderMock);

        $this->expectException(PaymentException::class);
        $this->paymentInfo->offsite(123);
    }
}
