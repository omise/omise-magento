<?php

namespace Omise\Payment\Test\Unit\Service;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Service\CreditmemoService as MagentoCreditmemoService;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Framework\Phrase;
use Omise\Payment\Service\CreditMemoService;
use PHPUnit\Framework\TestCase;
use Mockery as m;
use Exception;

/**
 * @coversDefaultClass \Omise\Payment\Service\CreditMemoService
 */
class CreditMemoServiceTest extends TestCase
{
    private CreditMemoService $service;
    private CreditmemoFactory $creditMemoFactory;
    private MagentoCreditmemoService $creditMemoService;
    private Invoice $invoice;
    private ObjectManagerHelper $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManagerHelper($this);

        $this->creditMemoFactory = m::mock(CreditmemoFactory::class);
        $this->creditMemoService = m::mock(MagentoCreditmemoService::class);
        $this->invoice = m::mock(Invoice::class);

        $this->service = $this->objectManager->getObject(
            CreditMemoService::class,
            [
                'creditMemoFactory' => $this->creditMemoFactory,
                'creditMemoService' => $this->creditMemoService,
                'invoice' => $this->invoice,
            ]
        );
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreateRefundsAllInvoices(): void
    {
        $orderMock = m::mock(Order::class);
        $invoice1 = m::mock(Invoice::class);
        $invoice2 = m::mock(Invoice::class);
        $creditMemoMock1 = m::mock(Creditmemo::class);
        $creditMemoMock2 = m::mock(Creditmemo::class);

        $orderMock->shouldReceive('getInvoiceCollection')->once()->andReturn([$invoice1, $invoice2]);
        $orderMock->shouldReceive('getIncrementId')->twice()->andReturn('100001');

        $invoice1->shouldReceive('getIncrementId')->once()->andReturn('INV001');
        $invoice2->shouldReceive('getIncrementId')->once()->andReturn('INV002');

        $this->invoice->shouldReceive('loadByIncrementId')->with('INV001')->once()->andReturn($invoice1);
        $this->invoice->shouldReceive('loadByIncrementId')->with('INV002')->once()->andReturn($invoice2);

        $this->creditMemoFactory->shouldReceive('createByOrder')
            ->with($orderMock)
            ->twice()
            ->andReturn($creditMemoMock1, $creditMemoMock2);

        foreach ([$creditMemoMock1, $creditMemoMock2] as $creditMemo) {
            $creditMemo->shouldReceive('setCustomerNote')->once()->with(m::type(Phrase::class))->andReturnSelf();
            $creditMemo->shouldReceive('setCustomerNoteNotify')->once()->with(false)->andReturnSelf();
            $creditMemo->shouldReceive('addComment')->once()->with(m::type(Phrase::class))->andReturnSelf();
        }

        $this->creditMemoService->shouldReceive('refund')->once()->with($creditMemoMock1);
        $this->creditMemoService->shouldReceive('refund')->once()->with($creditMemoMock2);

        $this->service->create($orderMock);

        $this->assertSame(1, $this->creditMemoFactory->mockery_getExpectationCount(), 'createByOrder called once');
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreateWithNoInvoicesDoesNothing(): void
    {
        $orderMock = m::mock(Order::class);
        $orderMock->shouldReceive('getInvoiceCollection')->once()->andReturn([]);

        $this->invoice->shouldReceive('loadByIncrementId')->never();
        $this->creditMemoFactory->shouldReceive('createByOrder')->never();
        $this->creditMemoService->shouldReceive('refund')->never();

        $this->service->create($orderMock);

        $this->assertTrue(true, 'No methods should be called when invoice collection is empty.');
    }

    /**
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreateHandlesCreditMemoException(): void
    {
        $orderMock = m::mock(Order::class);
        $invoice1 = m::mock(Invoice::class);
        $creditMemoMock = m::mock(Creditmemo::class);

        $orderMock->shouldReceive('getInvoiceCollection')->once()->andReturn([$invoice1]);
        $orderMock->shouldReceive('getIncrementId')->once()->andReturn('100003');

        $invoice1->shouldReceive('getIncrementId')->once()->andReturn('INV004');
        $this->invoice->shouldReceive('loadByIncrementId')->with('INV004')->once()->andReturn($invoice1);

        $this->creditMemoFactory->shouldReceive('createByOrder')->with($orderMock)->once()->andReturn($creditMemoMock);
        $creditMemoMock->shouldReceive('setCustomerNote')->once()->andThrow(new Exception('Error creating credit memo'));
        $this->creditMemoService->shouldReceive('refund')->never();

        try {
            $this->service->create($orderMock);
        } catch (Exception $e) {
            $this->assertSame('Error creating credit memo', $e->getMessage());
        }
    }
}