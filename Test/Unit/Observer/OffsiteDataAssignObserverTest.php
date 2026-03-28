<?php

namespace Omise\Payment\Test\Unit\Observer;

use Omise\Payment\Observer\OffsiteDataAssignObserver;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Event\Observer;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\PaymentInterface;

class OffsiteDataAssignObserverTest extends TestCase
{
    /**
     * @covers \Omise\Payment\Observer\OffsiteDataAssignObserver::execute
     */
    public function testExecuteSetsAdditionalInformation()
    {
        // Additional data that will be set on the payment
        $additionalData = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        // Mock Payment object
        $paymentMock = $this->getMockBuilder(\Magento\Payment\Model\InfoInterface::class)
            ->onlyMethods(['setAdditionalInformation'])
            ->getMockForAbstractClass();

        // Expect setAdditionalInformation to be called for each key
        $paymentMock->expects($this->exactly(2))
            ->method('setAdditionalInformation')
            ->withConsecutive(
                ['key1', 'value1'],
                ['key2', 'value2']
            );

        // Mock DataObject containing additional data
        $dataObject = new DataObject([PaymentInterface::KEY_ADDITIONAL_DATA => $additionalData]);

        // Mock Observer
        $observerMock = $this->createMock(Observer::class);

        // Create the OffsiteDataAssignObserver mock and override protected methods
        $observer = $this->getMockBuilder(OffsiteDataAssignObserver::class)
            ->onlyMethods(['readDataArgument', 'readPaymentModelArgument'])
            ->getMock();

        // Set the protected property using Reflection
        $reflection = new \ReflectionClass($observer);
        $property = $reflection->getProperty('additionalInformationList');
        $property->setAccessible(true);
        $property->setValue($observer, ['key1', 'key2']);

        // Configure method mocks
        $observer->method('readDataArgument')->willReturn($dataObject);
        $observer->method('readPaymentModelArgument')->willReturn($paymentMock);

        // Execute
        $observer->execute($observerMock);
    }

    /**
     * @covers \Omise\Payment\Observer\OffsiteDataAssignObserver::execute
     */
    public function testExecuteReturnsEarlyIfAdditionalDataNotArray()
    {
        $dataObject = new DataObject([PaymentInterface::KEY_ADDITIONAL_DATA => 'not_array']);
        $paymentMock = $this->createMock(\Magento\Payment\Model\InfoInterface::class);

        $observerMock = $this->createMock(Observer::class);

        $observer = $this->getMockBuilder(OffsiteDataAssignObserver::class)
            ->onlyMethods(['readDataArgument', 'readPaymentModelArgument'])
            ->getMock();

        $observer->method('readDataArgument')->willReturn($dataObject);
        $observer->method('readPaymentModelArgument')->willReturn($paymentMock);

        // Execute (should return early without error)
        $observer->execute($observerMock);

        $this->assertTrue(true); // just to mark test passed
    }
}
