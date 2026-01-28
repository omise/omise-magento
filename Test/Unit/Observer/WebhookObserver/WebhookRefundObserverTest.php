<?php

namespace Omise\Payment\Test\Unit\Observer\WebhookObserver;

use Omise\Payment\Observer\WebhookObserver\WebhookRefundObserver;
use Omise\Payment\Model\Api\Event as ApiEvent;
use Omise\Payment\Model\Order as OmiseOrder;
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Service\CreditMemoService;
use Magento\Sales\Model\Order as MagentoOrder;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Omise\Payment\Observer\WebhookObserver\WebhookRefundObserver
 * @covers \Omise\Payment\Observer\WebhookObserver\WebhookObserver
 */
class WebhookRefundObserverTest extends TestCase
{
    private $apiEventMock;
    private $orderMock;
    private $configMock;
    private $creditMemoServiceMock;
    private $observer;

    protected function setUp(): void
    {
        $this->apiEventMock = $this->createMock(ApiEvent::class);
        $this->configMock = $this->createMock(Config::class);
        $this->creditMemoServiceMock = $this->createMock(CreditMemoService::class);

        // Mock OmiseOrder and allow dynamic methods used in execute
        $this->orderMock = $this->getMockBuilder(OmiseOrder::class)
            ->disableOriginalConstructor()
            ->addMethods([
                'isPaymentReview',
                'getState',
                'addStatusHistoryComment',
                'save',
                'getOrderCurrencyCode'
            ])
            ->getMock();

        // Initialize observer with mocked dependencies
        $this->observer = $this->getMockBuilder(WebhookRefundObserver::class)
            ->setConstructorArgs([
                $this->apiEventMock,
                $this->orderMock,
                $this->configMock,
                $this->creditMemoServiceMock
            ])
            ->onlyMethods(['setUpExecute'])
            ->getMock();
    }

    /**
     * @covers \Omise\Payment\Observer\WebhookObserver\WebhookRefundObserver::__construct
     * @covers \Omise\Payment\Observer\WebhookObserver\WebhookRefundObserver::execute
     * @covers \Omise\Payment\Observer\WebhookObserver\WebhookRefundObserver::closeOrder
     */
    public function testExecuteClosesOrderIfProcessing(): void
    {
        $observerMock = $this->createMock(Observer::class);

        // Simulate setUpExecute returning true
        $this->observer->method('setUpExecute')->willReturn(true);

        // Setup order mock to simulate processing order
        $this->orderMock->method('isPaymentReview')->willReturn(false);
        $this->orderMock->method('getState')->willReturn(MagentoOrder::STATE_PROCESSING);
        $this->orderMock->method('getOrderCurrencyCode')->willReturn('THB');
        $this->orderMock->expects($this->once())->method('addStatusHistoryComment');
        $this->orderMock->expects($this->once())->method('save');

        // Setup charge mock properly using addMethods
        $chargeMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['isFullyRefunded', 'getRefundedAmount'])
            ->getMock();
        $chargeMock->method('isFullyRefunded')->willReturn(true);
        $chargeMock->method('getRefundedAmount')->willReturn(100);

        // Inject protected properties using Reflection
        $reflection = new \ReflectionClass($this->observer);

        $orderProperty = $reflection->getProperty('orderData');
        $orderProperty->setAccessible(true);
        $orderProperty->setValue($this->observer, $this->orderMock);

        $chargeProperty = $reflection->getProperty('charge');
        $chargeProperty->setAccessible(true);
        $chargeProperty->setValue($this->observer, $chargeMock);

        // Execute
        $this->observer->execute($observerMock);
    }

    /**
     * @covers \Omise\Payment\Observer\WebhookObserver\WebhookRefundObserver::execute
     */
    public function testExecuteDoesNothingIfSetUpExecuteFails(): void
    {
        $observerMock = $this->createMock(Observer::class);

        // Simulate setUpExecute returning false
        $this->observer->method('setUpExecute')->willReturn(false);

        // Expect that order methods are never called
        $this->orderMock->expects($this->never())->method('addStatusHistoryComment');
        $this->orderMock->expects($this->never())->method('save');

        // Inject order mock using reflection
        $reflection = new \ReflectionClass($this->observer);
        $property = $reflection->getProperty('orderData');
        $property->setAccessible(true);
        $property->setValue($this->observer, $this->orderMock);

        // Execute
        $this->observer->execute($observerMock);
    }

}
