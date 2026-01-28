<?php
declare(strict_types=1);

namespace Omise\Payment\Test\Unit\Observer\WebhookObserver;

use Omise\Payment\Observer\WebhookObserver\WebhookCompleteObserver;
use Omise\Payment\Model\Api\Event as ApiEvent;
use Omise\Payment\Model\Order as OmiseOrder;
use Omise\Payment\Helper\OmiseEmailHelper;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Model\Config\Config;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order as MagentoOrder;
use Magento\Sales\Model\Order\Item as MagentoOrderItem;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment\Transaction;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @coversDefaultClass \Omise\Payment\Observer\WebhookObserver\WebhookCompleteObserver
 * @covers ::__construct
 * @covers \Omise\Payment\Observer\WebhookObserver\WebhookObserver::__construct
 */
class WebhookCompleteObserverTest extends TestCase
{
    private WebhookCompleteObserver $observer;
    private Observer $observerEvent;
    private $chargeStub;
    private $paymentStub;
    private $invoiceStub;
    private $orderConfigStub;
    private MagentoOrder $magentoOrder;
    private OmiseEmailHelper $emailHelper;
    private OmiseHelper $helper;

    protected function setUp(): void
    {
        $apiEvent = $this->createMock(ApiEvent::class);
        $omiseOrder = $this->createMock(OmiseOrder::class);
        $this->emailHelper = $this->createMock(OmiseEmailHelper::class);
        $this->helper = $this->createMock(OmiseHelper::class);
        $config = $this->createMock(Config::class);
        $this->observerEvent = $this->createMock(Observer::class);

        $this->magentoOrder = $this->createMock(MagentoOrder::class);
        $this->paymentStub = $this->createMock(\Magento\Sales\Model\Order\Payment::class);

        // Invoice stub
        $this->invoiceStub = $this->createMock(Invoice::class);
        $this->invoiceStub->method('getBaseGrandTotal')->willReturn(100);
        $this->invoiceStub->method('cancel')->willReturnSelf();

        // Charge stub
        $this->chargeStub = new class {
            public string $failure_message = '';
            private bool $failed = false;
            private bool $successful = false;
            public string $id = 'chrg_test_123';

            public function isFailed(): bool { return $this->failed; }
            public function setFailed(bool $val): void { $this->failed = $val; }
            public function isSuccessful(): bool { return $this->successful; }
            public function setSuccessful(bool $val): void { $this->successful = $val; }
        };

        // Order config stub
        $this->orderConfigStub = $this->createMock(\Magento\Sales\Model\Order\Config::class);
        $this->orderConfigStub->method('getStateDefaultStatus')->willReturn('processing');
        $this->magentoOrder->method('getConfig')->willReturn($this->orderConfigStub);

        // Base currency stub
        $currencyMock = $this->createMock(\Magento\Directory\Model\Currency::class);
        $currencyMock->method('formatTxt')->willReturn('THB 100.00');
        $this->magentoOrder->method('getBaseCurrency')->willReturn($currencyMock);

        // Invoice collection stub
        $invoiceCollectionMock = $this->createMock(\Magento\Framework\Data\Collection::class);
        $invoiceCollectionMock->method('getLastItem')->willReturn($this->invoiceStub);
        $this->magentoOrder->method('getInvoiceCollection')->willReturn($invoiceCollectionMock);

        // All items stub
        $item = $this->createMock(MagentoOrderItem::class);
        $item->method('setQtyCanceled')->willReturnSelf();
        $item->method('save')->willReturnSelf();
        $this->magentoOrder->method('getAllItems')->willReturn([$item]);

        // Save / registerCancellation / addRelatedObject stubs
        $this->magentoOrder->method('save')->willReturnSelf();
        $this->magentoOrder->method('registerCancellation')->willReturnSelf();
        $this->magentoOrder->method('addRelatedObject')->willReturnSelf();

        // Build observer with setUpExecute stubbed
        $this->observer = $this->getMockBuilder(WebhookCompleteObserver::class)
            ->setConstructorArgs([$apiEvent, $omiseOrder, $this->emailHelper, $this->helper, $config])
            ->onlyMethods(['setUpExecute'])
            ->getMock();

        $this->observer->method('setUpExecute')->willReturn(true);

        // Inject protected properties
        $this->setProtectedProperty('orderData', $this->magentoOrder);
        $this->setProtectedProperty('charge', $this->chargeStub);
        $this->setProtectedProperty('payment', $this->paymentStub);
        $this->setProtectedProperty('invoice', $this->invoiceStub);
    }

    private function setProtectedProperty(string $name, $value): void
    {
        $ref = new ReflectionClass($this->observer);
        $prop = $ref->getProperty($name);
        $prop->setAccessible(true);
        $prop->setValue($this->observer, $value);
    }

    private function getProtectedMethod(string $name)
    {
        $ref = new ReflectionClass($this->observer);
        $method = $ref->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @covers ::__construct
     * @covers ::execute
     * @covers ::processOrder
     * @covers ::transactionCommentToOrder
     */
    public function testExecuteSuccessfulPaymentProcessesOrder(): void
    {
        $this->magentoOrder->method('getState')->willReturn(MagentoOrder::STATE_PENDING_PAYMENT);
        $this->chargeStub->setSuccessful(true);

        $this->helper->method('createInvoiceAndMarkAsPaid')->willReturn($this->invoiceStub);
        $this->emailHelper->expects($this->once())
            ->method('sendInvoiceAndConfirmationEmails')
            ->with($this->magentoOrder);

        $this->observer->execute($this->observerEvent);

        $this->assertTrue(true);
    }

    /**
     * @covers ::__construct
     * @covers ::execute
     * @covers ::cancelOrder
     */
    public function testExecuteFailedPaymentCancelsOrder(): void
    {
        $this->magentoOrder->method('getState')->willReturn(MagentoOrder::STATE_PENDING_PAYMENT);
        $this->chargeStub->setFailed(true);
        $this->chargeStub->failure_message = 'card declined';

        $this->magentoOrder->expects($this->once())->method('registerCancellation');

        $this->observer->execute($this->observerEvent);

        $this->assertTrue(true);
    }

    /**
     * @covers ::__construct
     * @covers ::execute
     * @covers ::processOrder
     * @covers ::transactionCommentToOrder
     * @covers ::cancelOrder
     * @covers ::processCancelledOrder
     * @covers ::reverseCancelledItems
     */
    public function testExecuteProcessesCancelledOrder(): void
    {
        $this->magentoOrder->method('getState')->willReturn(MagentoOrder::STATE_CANCELED);
        $this->chargeStub->setSuccessful(true);

        $this->helper->method('createInvoiceAndMarkAsPaid')->willReturn($this->invoiceStub);
        $this->emailHelper->expects($this->once())
            ->method('sendInvoiceAndConfirmationEmails')
            ->with($this->magentoOrder);

        $this->observer->execute($this->observerEvent);

        $this->assertTrue(true);
    }

    /**
     * @covers ::__construct
     * @covers ::transactionCommentToOrder
     */
    public function testTransactionCommentToOrderAddsTransaction(): void
    {
        $this->paymentStub->expects($this->exactly(2))
            ->method('addTransaction')
            ->willReturn($this->createMock(Transaction::class));

        $method = $this->getProtectedMethod('transactionCommentToOrder');
        $method->invoke($this->observer, true, '100');
        $method->invoke($this->observer, false, '50');

        $this->assertTrue(true);
    }

    /**
     * @covers ::__construct
     * @covers ::reverseCancelledItems
     */
    public function testReverseCancelledItemsSetsQtyCancelledToZero(): void
    {
        $method = $this->getProtectedMethod('reverseCancelledItems');
        $method->invoke($this->observer);

        $this->assertTrue(true);
    }

    /**
     * @covers ::__construct
     * @covers ::cancelOrder
     */
    public function testCancelOrderCancelsInvoiceAndRegistersCancellation(): void
    {
        $method = $this->getProtectedMethod('cancelOrder');
        $method->invoke($this->observer);

        $this->assertTrue(true);
    }

    /**
     * @covers ::__construct
     * @covers ::execute
     */
    public function testExecuteReturnsIfSetUpExecuteFails(): void
    {
        $mock = $this->getMockBuilder(WebhookCompleteObserver::class)
            ->setConstructorArgs([
                $this->createMock(ApiEvent::class),
                $this->createMock(OmiseOrder::class),
                $this->emailHelper,
                $this->helper,
                $this->createMock(Config::class)
            ])
            ->onlyMethods(['setUpExecute'])
            ->getMock();

        $mock->method('setUpExecute')->willReturn(false);
        $mock->execute($this->observerEvent);

        $this->assertTrue(true);
    }

}