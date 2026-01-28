<?php

namespace Omise\Payment\Test\Unit\Model;

use Omise\Payment\Model\SyncStatus;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Helper\OmiseEmailHelper;
use Omise\Payment\Model\Config\Cc as Config;
use Omise\Payment\Model\RefundSyncStatus;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\TestCase;
use Mockery;
use Magento\Framework\Exception\LocalizedException;

class SyncStatusTest extends TestCase
{
    protected $helper;
    protected $emailHelper;
    protected $config;
    protected $refundSyncStatus;
    protected $syncStatus;

    protected function setUp(): void
    {
        $this->helper = Mockery::mock(OmiseHelper::class);
        $this->emailHelper = Mockery::mock(OmiseEmailHelper::class);
        $this->config = Mockery::mock(Config::class);
        $this->refundSyncStatus = Mockery::mock(RefundSyncStatus::class);

        $this->syncStatus = new SyncStatus(
            $this->helper,
            $this->emailHelper,
            $this->config,
            $this->refundSyncStatus
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * Helper to call private methods
     */
    private function callPrivate(string $method, ...$args)
    {
        $reflection = new \ReflectionClass($this->syncStatus);
        $m = $reflection->getMethod($method);
        $m->setAccessible(true);
        return $m->invoke($this->syncStatus, ...$args);
    }

    /** @covers \Omise\Payment\Model\SyncStatus::__construct
     *  @covers \Omise\Payment\Model\SyncStatus::cancelOrderInvoice
     */
    public function testCancelOrderInvoiceCancelsLastInvoice()
    {
        $invoice = Mockery::mock(Invoice::class);
        $invoice->shouldReceive('cancel')->once();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('hasInvoices')->andReturn(true);
        $order->shouldReceive('getInvoiceCollection')->andReturnSelf();
        $order->shouldReceive('getLastItem')->andReturn($invoice);
        $order->shouldReceive('addRelatedObject')->with($invoice)->once();

        $this->syncStatus->cancelOrderInvoice($order);

        $this->assertTrue(true);
    }

    /** @covers \Omise\Payment\Model\SyncStatus::__construct
     *  @covers \Omise\Payment\Model\SyncStatus::markPaymentFailed
     *  @covers \Omise\Payment\Model\SyncStatus::cancelOrderInvoice
     */
    public function testMarkPaymentFailed()
    {
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('hasInvoices')->andReturn(false);
        $order->shouldReceive('registerCancellation')->once()->andReturnSelf();
        $order->shouldReceive('save')->once();

        $this->callPrivate('markPaymentFailed', $order, [
            'failure_message' => 'Test fail',
            'failure_code' => '123'
        ]);

        $this->assertTrue(true);
    }

    /** @covers \Omise\Payment\Model\SyncStatus::__construct
     *  @covers \Omise\Payment\Model\SyncStatus::markOrderPending
     */
    public function testMarkOrderPending()
    {
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('addStatusHistoryComment')->once();
        $order->shouldReceive('getState')->andReturn('new');
        $order->shouldReceive('setState')->with(Order::STATE_PENDING_PAYMENT)->andReturnSelf();
        $order->shouldReceive('setStatus')->with(Order::STATE_PENDING_PAYMENT)->andReturnSelf();
        $order->shouldReceive('save')->once();

        $this->callPrivate('markOrderPending', $order);

        $this->assertTrue(true);
    }

    /** @covers \Omise\Payment\Model\SyncStatus::__construct
     *  @covers \Omise\Payment\Model\SyncStatus::markPaymentExpired
     *  @covers \Omise\Payment\Model\SyncStatus::cancelOrderInvoice
     */
    public function testMarkPaymentExpired()
    {
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('hasInvoices')->andReturn(false);
        $order->shouldReceive('registerCancellation')->once()->andReturnSelf();
        $order->shouldReceive('save')->once();

        $this->callPrivate('markPaymentExpired', $order);

        $this->assertTrue(true);
    }

    /** @covers \Omise\Payment\Model\SyncStatus::__construct
     *  @covers \Omise\Payment\Model\SyncStatus::markPaymentReversed
     *  @covers \Omise\Payment\Model\SyncStatus::cancelOrderInvoice
     */
    public function testMarkPaymentReversed()
    {
        $order = Mockery::mock(Order::class);
        $order->shouldReceive('hasInvoices')->andReturn(false);
        $order->shouldReceive('registerCancellation')->once()->andReturnSelf();
        $order->shouldReceive('save')->once();

        $this->callPrivate('markPaymentReversed', $order);

        $this->assertTrue(true);
    }

    /** @covers \Omise\Payment\Model\SyncStatus::__construct
     *  @covers \Omise\Payment\Model\SyncStatus::reverseCancelledItems
     */
    public function testReverseCancelledItems()
    {
        $item = Mockery::mock();
        $item->shouldReceive('setQtyCanceled')->with(0)->once();
        $item->shouldReceive('save')->once();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAllItems')->andReturn([$item]);

        $this->callPrivate('reverseCancelledItems', $order);

        $this->assertTrue(true);
    }

    /** @covers \Omise\Payment\Model\SyncStatus::__construct
     *  @covers \Omise\Payment\Model\SyncStatus::sync
     */
    public function testSyncThrowsExceptionWhenNoChargeId()
    {
        $order = Mockery::mock(Order::class);
        $this->helper->shouldReceive('getOrderChargeId')->with($order)->andReturn(null);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Unable to find Omise charge ID');

        $this->syncStatus->sync($order);

        $this->assertTrue(true);
    }

    /** @covers \Omise\Payment\Model\SyncStatus::__construct
     *  @covers \Omise\Payment\Model\SyncStatus::markPaymentSuccessful
     */
    public function testMarkPaymentSuccessful_RefundTriggered()
    {
        $charge = ['id' => 'ch_test'];

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getState')->andReturn('new');

        $this->refundSyncStatus->shouldReceive('shouldRefund')->with($charge)->andReturn(true);
        $this->refundSyncStatus->shouldReceive('refund')->with($order, $charge)->once();

        $this->callPrivate('markPaymentSuccessful', $order, $charge);

        $this->assertTrue(true);
    }

    /** @covers \Omise\Payment\Model\SyncStatus::__construct
     *  @covers \Omise\Payment\Model\SyncStatus::markPaymentSuccessful
     *  @covers \Omise\Payment\Model\SyncStatus::reverseCancelledItems
     */
    public function testMarkPaymentSuccessful_OrderCanceled()
    {
        $charge = ['id' => 'ch_test'];

        $item = Mockery::mock();
        $item->shouldReceive('setQtyCanceled')->with(0)->once();
        $item->shouldReceive('save')->once();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getState')->andReturn(Order::STATE_CANCELED);
        $order->shouldReceive('getAllItems')->andReturn([$item]);
        $order->shouldReceive('setState')->with(Order::STATE_PROCESSING)->once();
        $order->shouldReceive('getConfig')->andReturnSelf();
        $order->shouldReceive('getStateDefaultStatus')->with(Order::STATE_PROCESSING)->andReturn('processing');
        $order->shouldReceive('setStatus')->with('processing')->once();
        $order->shouldReceive('getGrandTotal')->andReturn(100);
        $order->shouldReceive('getOrderCurrencyCode')->andReturn('THB');
        $order->shouldReceive('addStatusHistoryComment')->once();
        $order->shouldReceive('save')->once();

        $this->helper->shouldReceive('createInvoiceAndMarkAsPaid')->with($order, 'ch_test')->once();
        $this->emailHelper->shouldReceive('sendInvoiceAndConfirmationEmails')->with($order)->once();

        $this->refundSyncStatus->shouldReceive('shouldRefund')->with($charge)->andReturn(false);

        $this->callPrivate('markPaymentSuccessful', $order, $charge);

        $this->assertTrue(true);
    }

    /** @covers \Omise\Payment\Model\SyncStatus::__construct
     *  @covers \Omise\Payment\Model\SyncStatus::markPaymentSuccessful
     */
    public function testMarkPaymentSuccessful_OrderAlreadyProcessed()
    {
        $charge = ['id' => 'ch_test'];

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getState')->andReturn(Order::STATE_COMPLETE);

        $this->refundSyncStatus->shouldReceive('shouldRefund')->with($charge)->andReturn(false);

        $order->shouldNotReceive('setState');
        $order->shouldNotReceive('setStatus');
        $order->shouldNotReceive('addStatusHistoryComment');
        $order->shouldNotReceive('save');

        $this->callPrivate('markPaymentSuccessful', $order, $charge);

        $this->assertTrue(true);
    }

    /** @covers \Omise\Payment\Model\SyncStatus::__construct
     *  @covers \Omise\Payment\Model\SyncStatus::markPaymentSuccessful
     */
    public function testMarkPaymentSuccessful_NormalProcessing()
    {
        $charge = ['id' => 'ch_test'];

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getState')->andReturn('new');
        $order->shouldReceive('setState')->with(Order::STATE_PROCESSING)->once();
        $order->shouldReceive('getConfig')->andReturnSelf();
        $order->shouldReceive('getStateDefaultStatus')->with(Order::STATE_PROCESSING)->andReturn('processing');
        $order->shouldReceive('setStatus')->with('processing')->once();
        $order->shouldReceive('getGrandTotal')->andReturn(100);
        $order->shouldReceive('getOrderCurrencyCode')->andReturn('THB');
        $order->shouldReceive('addStatusHistoryComment')->once();
        $order->shouldReceive('save')->once();

        $this->refundSyncStatus->shouldReceive('shouldRefund')->with($charge)->andReturn(false);
        $this->helper->shouldReceive('createInvoiceAndMarkAsPaid')->with($order, 'ch_test')->once();
        $this->emailHelper->shouldReceive('sendInvoiceAndConfirmationEmails')->with($order)->once();

        $this->callPrivate('markPaymentSuccessful', $order, $charge);

        $this->assertTrue(true);
    }

    /**
     * Helper to create fresh SyncStatus instance and mocks per test
     */
    private function createSyncStatusWithFreshMocks(&$order, &$helper, &$emailHelper, &$config, &$refundSyncStatus)
    {
        $helper = Mockery::mock(OmiseHelper::class);
        $emailHelper = Mockery::mock(OmiseEmailHelper::class);
        $config = Mockery::mock(Config::class);
        $refundSyncStatus = Mockery::mock(RefundSyncStatus::class);

        $syncStatus = new SyncStatus($helper, $emailHelper, $config, $refundSyncStatus);

        // Order store
        $store = Mockery::mock();
        $order->shouldReceive('getStore')->andReturn($store);
        $store->shouldReceive('getId')->andReturn(1);

        // Config
        $config->shouldReceive('setStoreId')->with(1);
        $config->shouldReceive('getPublicKey')->andReturn('pkey');
        $config->shouldReceive('getSecretKey')->andReturn('skey');

        return $syncStatus;
    }

    /**
     * @covers \Omise\Payment\Model\SyncStatus::sync
     * @covers \Omise\Payment\Model\SyncStatus::__construct
     * @covers \Omise\Payment\Model\SyncStatus::markPaymentSuccessful
     */
    public function testSyncStatusSuccessful()
    {
        $order = Mockery::mock(Order::class);

        $syncStatus = $this->createSyncStatusWithFreshMocks($order, $helper, $emailHelper, $config, $refundSyncStatus);

        $chargeMock = ['status' => SyncStatus::STATUS_SUCCESSFUL, 'id' => 'ch_test'];

        $helper->shouldReceive('getOrderChargeId')->with($order)->andReturn('ch_test');
        $refundSyncStatus->shouldReceive('shouldRefund')->with($chargeMock)->andReturn(false);

        // Side effects of markPaymentSuccessful
        $helper->shouldReceive('createInvoiceAndMarkAsPaid')->with($order, 'ch_test')->once();
        $emailHelper->shouldReceive('sendInvoiceAndConfirmationEmails')->with($order)->once();

        // Order state changes
        $order->shouldReceive('getState')->andReturn(Order::STATE_NEW);
        $order->shouldReceive('getConfig')->andReturnSelf();
        $order->shouldReceive('getStateDefaultStatus')->with(Order::STATE_PROCESSING)->andReturn('processing');
        $order->shouldReceive('setState')->with(Order::STATE_PROCESSING)->andReturnSelf();
        $order->shouldReceive('setStatus')->with('processing')->andReturnSelf();
        $order->shouldReceive('getGrandTotal')->andReturn(100);
        $order->shouldReceive('getOrderCurrencyCode')->andReturn('THB');
        $order->shouldReceive('addStatusHistoryComment')->andReturnSelf();
        $order->shouldReceive('save')->andReturnSelf();

        // Mock static OmiseCharge call
        $omiseChargeMock = Mockery::mock('alias:\OmiseCharge');
        $omiseChargeMock->shouldReceive('retrieve')->andReturn($chargeMock);

        $syncStatus->sync($order);
        $this->assertTrue(true);
    }

    /**
     * @covers \Omise\Payment\Model\SyncStatus::sync
     * @covers \Omise\Payment\Model\SyncStatus::__construct
     * @covers \Omise\Payment\Model\SyncStatus::markPaymentFailed
     * @covers \Omise\Payment\Model\SyncStatus::cancelOrderInvoice
     */
    public function testSyncStatusFailed()
    {
        $order = Mockery::mock(Order::class);

        $syncStatus = $this->createSyncStatusWithFreshMocks($order, $helper, $emailHelper, $config, $refundSyncStatus);

        $chargeMock = [
            'status' => SyncStatus::STATUS_FAILED,
            'id' => 'ch_test',
            'failure_message' => 'fail',
            'failure_code' => '123'
        ];

        $helper->shouldReceive('getOrderChargeId')->with($order)->andReturn('ch_test');
        $refundSyncStatus->shouldReceive('shouldRefund')->with($chargeMock)->andReturn(false);

        $order->shouldReceive('hasInvoices')->andReturn(false);
        $order->shouldReceive('registerCancellation')->once()->andReturnSelf();
        $order->shouldReceive('save')->once();

        $omiseChargeMock = Mockery::mock('alias:\OmiseCharge');
        $omiseChargeMock->shouldReceive('retrieve')->andReturn($chargeMock);

        $syncStatus->sync($order);
        $this->assertTrue(true);
    }

    /**
     * @covers \Omise\Payment\Model\SyncStatus::sync
     * @covers \Omise\Payment\Model\SyncStatus::__construct
     * @covers \Omise\Payment\Model\SyncStatus::markOrderPending
     */
    public function testSyncStatusPending()
    {
        $order = Mockery::mock(Order::class);

        $syncStatus = $this->createSyncStatusWithFreshMocks($order, $helper, $emailHelper, $config, $refundSyncStatus);

        $chargeMock = ['status' => SyncStatus::STATUS_PENDING, 'id' => 'ch_test'];

        $helper->shouldReceive('getOrderChargeId')->with($order)->andReturn('ch_test');
        $refundSyncStatus->shouldReceive('shouldRefund')->with($chargeMock)->andReturn(false);

        $order->shouldReceive('addStatusHistoryComment')->once()->andReturnSelf();
        $order->shouldReceive('getState')->andReturn('new');
        $order->shouldReceive('setState')->with(Order::STATE_PENDING_PAYMENT)->andReturnSelf();
        $order->shouldReceive('setStatus')->with(Order::STATE_PENDING_PAYMENT)->andReturnSelf();
        $order->shouldReceive('save')->once()->andReturnSelf();

        $omiseChargeMock = Mockery::mock('alias:\OmiseCharge');
        $omiseChargeMock->shouldReceive('retrieve')->andReturn($chargeMock);

        $syncStatus->sync($order);
        $this->assertTrue(true);
    }

    /**
     * @covers \Omise\Payment\Model\SyncStatus::sync
     * @covers \Omise\Payment\Model\SyncStatus::__construct
     * @covers \Omise\Payment\Model\SyncStatus::markPaymentExpired
     * @covers \Omise\Payment\Model\SyncStatus::cancelOrderInvoice
     */
    public function testSyncStatusExpired()
    {
        $order = Mockery::mock(Order::class);

        $syncStatus = $this->createSyncStatusWithFreshMocks($order, $helper, $emailHelper, $config, $refundSyncStatus);

        $chargeMock = ['status' => SyncStatus::STATUS_EXPIRED, 'id' => 'ch_test'];

        $helper->shouldReceive('getOrderChargeId')->with($order)->andReturn('ch_test');
        $refundSyncStatus->shouldReceive('shouldRefund')->with($chargeMock)->andReturn(false);

        $order->shouldReceive('hasInvoices')->andReturn(false);
        $order->shouldReceive('registerCancellation')->once()->andReturnSelf();
        $order->shouldReceive('save')->once();

        $omiseChargeMock = Mockery::mock('alias:\OmiseCharge');
        $omiseChargeMock->shouldReceive('retrieve')->andReturn($chargeMock);

        $syncStatus->sync($order);
        $this->assertTrue(true);
    }

    /**
     * @covers \Omise\Payment\Model\SyncStatus::sync
     * @covers \Omise\Payment\Model\SyncStatus::__construct
     * @covers \Omise\Payment\Model\SyncStatus::markPaymentReversed
     * @covers \Omise\Payment\Model\SyncStatus::cancelOrderInvoice
     */
    public function testSyncStatusReversed()
    {
        $order = Mockery::mock(Order::class);

        $syncStatus = $this->createSyncStatusWithFreshMocks($order, $helper, $emailHelper, $config, $refundSyncStatus);

        $chargeMock = ['status' => SyncStatus::STATUS_REVERSED, 'id' => 'ch_test'];

        $helper->shouldReceive('getOrderChargeId')->with($order)->andReturn('ch_test');
        $refundSyncStatus->shouldReceive('shouldRefund')->with($chargeMock)->andReturn(false);

        $order->shouldReceive('hasInvoices')->andReturn(false);
        $order->shouldReceive('registerCancellation')->once()->andReturnSelf();
        $order->shouldReceive('save')->once();

        $omiseChargeMock = Mockery::mock('alias:\OmiseCharge');
        $omiseChargeMock->shouldReceive('retrieve')->andReturn($chargeMock);

        $syncStatus->sync($order);
        $this->assertTrue(true);
    }

    /**
     * @covers \Omise\Payment\Model\SyncStatus::sync
     * @covers \Omise\Payment\Model\SyncStatus::__construct
     */
    public function testSyncThrowsExceptionForUnknownStatus()
    {
        $order = Mockery::mock(Order::class);

        $syncStatus = $this->createSyncStatusWithFreshMocks($order, $helper, $emailHelper, $config, $refundSyncStatus);

        $chargeMock = ['status' => 'unknown_status', 'id' => 'ch_test'];

        $helper->shouldReceive('getOrderChargeId')->with($order)->andReturn('ch_test');

        $omiseChargeMock = Mockery::mock('alias:\OmiseCharge');
        $omiseChargeMock->shouldReceive('retrieve')->andReturn($chargeMock);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Cannot read the payment status');

        $syncStatus->sync($order);
    }
}
