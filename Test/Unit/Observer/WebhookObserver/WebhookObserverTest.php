<?php

namespace Omise\Payment\Test\Unit\Observer\WebhookObserver;

use PHPUnit\Framework\TestCase;
use Omise\Payment\Observer\WebhookObserver\WebhookObserver;
use Omise\Payment\Model\Order;
use Omise\Payment\Model\Api\Event as ApiEvent;
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Model\Api\Charge as ApiCharge;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order as MagentoOrder;

/**
 * @covers \Omise\Payment\Observer\WebhookObserver\WebhookObserver
 */
class WebhookObserverTest extends TestCase
{
    private ApiEvent $apiEventMock;
    private Order $orderMock;
    private Config $configMock;
    private Observer $observerMock;
    private ApiCharge $chargeMock;
    private Payment $paymentMock;
    private MagentoOrder $magentoOrderMock;

    protected function setUp(): void
    {
        $this->apiEventMock = $this->createMock(ApiEvent::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->configMock = $this->createMock(Config::class);

        $this->observerMock = $this->createMock(Observer::class);
        $this->chargeMock = $this->createMock(ApiCharge::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->magentoOrderMock = $this->createMock(MagentoOrder::class);
    }

    /**
     * Create a concrete subclass to test the abstract WebhookObserver
     */
    private function getTestObserver(): WebhookObserver
    {
        return new class($this->apiEventMock, $this->orderMock, $this->configMock) extends WebhookObserver {
            public function execute(Observer $observer)
            {
                // no-op
            }
        };
    }

    public function testConstructorSetsDependencies(): void
    {
        $observer = $this->getTestObserver();

        $this->assertInstanceOf(WebhookObserver::class, $observer);

        $reflection = new \ReflectionClass($observer);
        $this->assertFalse($reflection->hasProperty('apiEvent'));
        $this->assertTrue($reflection->hasProperty('order'));
        $this->assertFalse($reflection->hasProperty('config'));
    }

    public function testSetUpExecuteReturnsFalseForInvalidCharge(): void
    {
        $observer = $this->getTestObserver();
        $this->observerMock->method('getData')->with('data')->willReturn(null);

        $this->assertFalse($observer->setUpExecute($this->observerMock));
    }

    public function testSetUpExecuteReturnsFalseForMissingOrderId(): void
    {
        $observer = $this->getTestObserver();
        $this->chargeMock->method('getMetadata')->with('order_id')->willReturn(null);
        $this->observerMock->method('getData')->with('data')->willReturn($this->chargeMock);

        $this->assertFalse($observer->setUpExecute($this->observerMock));
    }

    public function testSetUpExecuteReturnsFalseForNonExistingOrder(): void
    {
        $observer = $this->getTestObserver();
        $this->chargeMock->method('getMetadata')->with('order_id')->willReturn('1001');
        $this->observerMock->method('getData')->with('data')->willReturn($this->chargeMock);

        $this->orderMock->method('loadByIncrementId')->with('1001')->willReturn($this->magentoOrderMock);
        $this->magentoOrderMock->method('getId')->willReturn(null);

        $this->assertFalse($observer->setUpExecute($this->observerMock));
    }

    public function testSetUpExecuteReturnsFalseForMissingPayment(): void
    {
        $observer = $this->getTestObserver();
        $this->chargeMock->method('getMetadata')->with('order_id')->willReturn('1001');
        $this->observerMock->method('getData')->with('data')->willReturn($this->chargeMock);

        $this->magentoOrderMock->method('getId')->willReturn(1);
        $this->magentoOrderMock->method('getPayment')->willReturn(null);

        $this->orderMock->method('loadByIncrementId')->with('1001')->willReturn($this->magentoOrderMock);

        $this->assertFalse($observer->setUpExecute($this->observerMock));
    }

    public function testSetUpExecuteReturnsTrueForValidCharge(): void
    {
        $observer = $this->getTestObserver();
        $this->chargeMock->method('getMetadata')->with('order_id')->willReturn('1001');
        $this->observerMock->method('getData')->with('data')->willReturn($this->chargeMock);

        $this->magentoOrderMock->method('getId')->willReturn(1);
        $this->magentoOrderMock->method('getPayment')->willReturn($this->paymentMock);

        $this->orderMock->method('loadByIncrementId')->with('1001')->willReturn($this->magentoOrderMock);

        $this->assertTrue($observer->setUpExecute($this->observerMock));
    }
}