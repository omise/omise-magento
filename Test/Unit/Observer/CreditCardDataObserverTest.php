<?php
// ------------------------
// Stub for Magento PaymentInterface to run tests in isolation
// ------------------------
namespace Magento\Payment\Api\Data;

interface PaymentInterface
{
    const KEY_ADDITIONAL_DATA = 'additional_data';
}

namespace Omise\Payment\Test\Unit\Observer;

use Omise\Payment\Observer\CreditCardDataObserver;
use Omise\Payment\Model\Customer;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Event\Observer;
use Magento\Framework\DataObject;
use Magento\Payment\Api\Data\PaymentInterface;
use Magento\Payment\Model\InfoInterface;

class CreditCardDataObserverTest extends TestCase
{
    private Customer $customerMock;
    private CreditCardDataObserver $observer;

    protected function setUp(): void
    {
        $this->customerMock = $this->createMock(Customer::class);
        $this->observer = new CreditCardDataObserver($this->customerMock);
    }

    /**
     * @covers \Omise\Payment\Observer\CreditCardDataObserver::__construct
     */
    public function testConstructorSetsCustomer(): void
    {
        $reflection = new \ReflectionClass($this->observer);
        $property = $reflection->getProperty('customer');
        $property->setAccessible(true);

        $this->assertSame($this->customerMock, $property->getValue($this->observer));
    }

    /**
     * @covers \Omise\Payment\Observer\CreditCardDataObserver::__construct
     * @covers \Omise\Payment\Observer\CreditCardDataObserver::execute
     * @covers \Omise\Payment\Observer\CreditCardDataObserver::maybeUseExistingCard
     * @covers \Omise\Payment\Observer\CreditCardDataObserver::maybeSaveCustomerCard
     * @covers \Omise\Payment\Observer\CreditCardDataObserver::setPaymentAdditionalInformation
     */
    public function testExecuteWithNewCardAndRememberCard(): void
    {
        $additionalData = [
            CreditCardDataObserver::TOKEN => 'tok_123',
            CreditCardDataObserver::REMEMBER_CARD => true
        ];

        $paymentMock = $this->createMock(InfoInterface::class);
        $paymentMock->expects($this->exactly(4))
            ->method('setAdditionalInformation')
            ->withConsecutive(
                [CreditCardDataObserver::TOKEN, 'tok_123'],
                [CreditCardDataObserver::CARD, 'card_456'],
                [CreditCardDataObserver::REMEMBER_CARD, true],
                [CreditCardDataObserver::CUSTOMER, 'cust_789']
            );

        // Use real DataObject
        $dataObject = new DataObject([
            PaymentInterface::KEY_ADDITIONAL_DATA => $additionalData
        ]);

        $observerMock = $this->createMock(Observer::class);
        $observerMock->method('getData')->willReturn($dataObject);

        $this->customerMock->expects($this->once())
            ->method('addCard')
            ->with('tok_123')
            ->willReturnSelf();

        $this->customerMock->expects($this->once())
            ->method('getLatestCard')
            ->willReturn(['id' => 'card_456']);

        $this->customerMock->expects($this->once())
            ->method('getId')
            ->willReturn('cust_789');

        // Override protected methods readDataArgument and readPaymentModelArgument
        $observerTest = $this->getMockBuilder(CreditCardDataObserver::class)
            ->setConstructorArgs([$this->customerMock])
            ->onlyMethods(['readDataArgument', 'readPaymentModelArgument'])
            ->getMock();

        $observerTest->expects($this->any())
            ->method('readDataArgument')
            ->with($observerMock)
            ->willReturn($dataObject);

        $observerTest->expects($this->any())
            ->method('readPaymentModelArgument')
            ->with($observerMock)
            ->willReturn($paymentMock);

        $observerTest->execute($observerMock);
    }

    /**
     * @covers \Omise\Payment\Observer\CreditCardDataObserver::__construct
     * @covers \Omise\Payment\Observer\CreditCardDataObserver::maybeUseExistingCard
     */
    public function testMaybeUseExistingCardAddsCustomer(): void
    {
        $additionalData = [
            CreditCardDataObserver::CARD => 'card_123'
        ];

        $this->customerMock->expects($this->once())
            ->method('getId')
            ->willReturn('cust_123');

        $reflection = new \ReflectionClass($this->observer);
        $method = $reflection->getMethod('maybeUseExistingCard');
        $method->setAccessible(true);

        $method->invokeArgs($this->observer, [&$additionalData]);

        $this->assertEquals('cust_123', $additionalData[CreditCardDataObserver::CUSTOMER]);
    }

    /**
     * @covers \Omise\Payment\Observer\CreditCardDataObserver::__construct
     * @covers \Omise\Payment\Observer\CreditCardDataObserver::maybeSaveCustomerCard
     */
    public function testMaybeSaveCustomerCard(): void
    {
        $additionalData = [
            CreditCardDataObserver::REMEMBER_CARD => true,
            CreditCardDataObserver::TOKEN => 'tok_abc'
        ];

        $this->customerMock->expects($this->once())
            ->method('addCard')
            ->with('tok_abc')
            ->willReturnSelf();

        $this->customerMock->expects($this->once())
            ->method('getLatestCard')
            ->willReturn(['id' => 'card_xyz']);

        $this->customerMock->expects($this->once())
            ->method('getId')
            ->willReturn('cust_789');

        $reflection = new \ReflectionClass($this->observer);
        $method = $reflection->getMethod('maybeSaveCustomerCard');
        $method->setAccessible(true);

        $method->invokeArgs($this->observer, [&$additionalData]);

        $this->assertEquals('card_xyz', $additionalData[CreditCardDataObserver::CARD]);
        $this->assertEquals('cust_789', $additionalData[CreditCardDataObserver::CUSTOMER]);
    }

    /**
     * @covers \Omise\Payment\Observer\CreditCardDataObserver::__construct
     * @covers \Omise\Payment\Observer\CreditCardDataObserver::setPaymentAdditionalInformation
     */
    public function testSetPaymentAdditionalInformation(): void
    {
        $additionalData = [
            CreditCardDataObserver::TOKEN => 'tok_1',
            CreditCardDataObserver::CARD => 'card_1',
            CreditCardDataObserver::REMEMBER_CARD => true,
            CreditCardDataObserver::CUSTOMER => 'cust_1',
            CreditCardDataObserver::CHARGE_ID => 'ch_1'
        ];

        $paymentMock = $this->createMock(InfoInterface::class);
        $paymentMock->expects($this->exactly(5))
            ->method('setAdditionalInformation')
            ->withConsecutive(
                ['omise_card_token', 'tok_1'],
                ['omise_card', 'card_1'],
                ['omise_save_card', true],
                ['customer', 'cust_1'],
                ['charge_id', 'ch_1']
            );

        $reflection = new \ReflectionClass($this->observer);
        $method = $reflection->getMethod('setPaymentAdditionalInformation');
        $method->setAccessible(true);

        $method->invokeArgs($this->observer, [$paymentMock, $additionalData]);
    }
}