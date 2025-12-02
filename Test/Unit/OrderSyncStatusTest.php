<?php

namespace Omise\Payment\Test\Unit;

use PHPUnit\Framework\TestCase;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Omise\Payment\Model\SyncStatus;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Omise\Payment\Model\Config\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Omise\Payment\Cron\OrderSyncStatus;
use ReflectionClass;
use stdClass;
use Mockery as m;
use Exception;
use ReflectionMethod;

class OrderSyncStatusTest extends TestCase
{
    private $orderCollectionFactory;

    private $orderRepository;

    private $syncStatus;

    private $configWriter;

    private $config;

    private $cacheTypeList;

    private $cacheFrontendPool;

    private $scopeConfig;

    protected function setUp(): void
    {
        $this->orderCollectionFactory = m::mock(CollectionFactory::class);
        $this->orderRepository = m::mock(OrderRepositoryInterface::class);
        $this->syncStatus = m::mock(SyncStatus::class);
        $this->configWriter = m::mock(WriterInterface::class);
        $this->config = m::mock(Config::class);
        $this->cacheTypeList = m::mock(TypeListInterface::class);
        $this->cacheFrontendPool = m::mock(Pool::class);
        $this->scopeConfig = m::mock(ScopeConfigInterface::class);

        parent::setUp();
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    private function getOrderIdsMock($times = 20)
    {
        $orderIds = [];

        for ($i=1; $i <= $times; $i++) {
            $orderIds[] = ['entity_id' => $i];
        }

        return $orderIds;
    }

    /**
     * Data provider for testExecuteMethod method
     */
    public function executeMethodDataProvier()
    {
        return [
            [
                "order_count" => 20,
                "is_expired" => true,
                // functions inside foreach loop
                "execution_times" => 20,
                // fucntions that runs only if charge is expired
                "conditional_execution_times" => 20
            ],
            [
                "order_count" => 10,
                "is_expired" => true,
                "execution_times" => 10,
                "conditional_execution_times" => 10
            ],
            [
                "order_count" => 20,
                "is_expired" => false,
                "execution_times" => 20,
                "conditional_execution_times" => 0
            ],
            [
                "order_count" => 10,
                "is_expired" => false,
                "execution_times" => 10,
                "conditional_execution_times" => 0
            ],
            [
                "order_count" => 0,
                "is_expired" => false,
                "execution_times" => 0,
                "conditional_execution_times" => 0
            ],
        ];
    }

    /**
     * Data provider for testIsExpiredMethod method
     */
    public function isExpiredDataProvier()
    {
        return [
            ["isExpired" => true, "expected" => true],
            ["isExpired" => false, "expected" => false],
        ];
    }

    /**
     * @dataProvider executeMethodDataProvier
     * @covers Omise\Payment\Cron\OrderSyncStatus
     */
    public function testExecuteMethod(
        $order_count,
        $is_expired,
        $execution_times,
        $conditional_execution_times
    ) {
        $this->mockScopeConfig();

        $orderMock = $this->mockOrder($execution_times, $conditional_execution_times);

        $this->mockOrderRepository($execution_times, $orderMock);

        $this->mockConfig($execution_times);

        $this->mockOmiseCharge($execution_times, $is_expired);

        $this->mockSyncStatus($conditional_execution_times, $orderMock);

        $mockCron = m::mock(
            OrderSyncStatus::class,
            [
                $this->orderCollectionFactory,
                $this->orderRepository,
                $this->scopeConfig,
                $this->syncStatus,
                $this->configWriter,
                $this->config,
                $this->cacheTypeList,
                $this->cacheFrontendPool
            ]
        )->makePartial();

        // Mocking `saveLastOrderId` method
        $mockCron->shouldReceive('saveLastOrderId')
            ->once();

        // Mocking `getOrderIds` method
        $mockCron->shouldReceive('getOrderIds')
            ->once()
            ->andReturn($this->getOrderIdsMock($order_count));

        $result = $mockCron->execute();

        $this->assertEquals(get_class($result), get_class($mockCron));
    }

    /**
     * @covers Omise\Payment\Cron\OrderSyncStatus
     */
    public function testExceptionOnExecuteMethod()
    {
        $this->config->shouldReceive('getValue')
            ->once()
            ->with('enable_cron_autoexpirysync')
            ->andReturn(true);

        $this->scopeConfig->shouldReceive('getValue')
            ->once()
            ->with('payment/omise/cron_last_order_id')
            ->andThrow(new Exception('Error message'));

        $mockCron = m::mock(
            OrderSyncStatus::class,
            [
                $this->orderCollectionFactory,
                $this->orderRepository,
                $this->scopeConfig,
                $this->syncStatus,
                $this->configWriter,
                $this->config,
                $this->cacheTypeList,
                $this->cacheFrontendPool
            ]
        )->makePartial();

        $mockCron->shouldReceive('saveLastOrderId')
            ->once();

        $result = $mockCron->execute();
        $this->assertEquals(get_class($result), get_class($mockCron));
    }

    /**
     * @dataProvider isExpiredDataProvier
     * @covers Omise\Payment\Cron\OrderSyncStatus
     */
    public function testIsExpiredMethod($isExpired, $expected)
    {
        $orderMock = m::mock(stdClass::class);
        $this->mockOrderPayment($orderMock, 1);

        $this->mockConfigPublicKeyAndPrivateKey(1);
        $this->mockOmiseCharge(1, $isExpired);

        $mockCron = m::mock(
            OrderSyncStatus::class,
            [
                $this->orderCollectionFactory,
                $this->orderRepository,
                $this->scopeConfig,
                $this->syncStatus,
                $this->configWriter,
                $this->config,
                $this->cacheTypeList,
                $this->cacheFrontendPool
            ]
        )->makePartial();

        $reflection = new ReflectionClass(OrderSyncStatus::class);
        $property = $reflection->getProperty('order');
        $property->setAccessible(true);
        $property->setValue($mockCron, $orderMock);

        $method = $reflection->getMethod('isExpired');
        $method->setAccessible(true);

        $result = $method->invokeArgs($mockCron, []);

        $this->assertEquals($result, $expected);
    }

    /**
     * @covers Omise\Payment\Cron\OrderSyncStatus
     */
    public function testGetOrderIds()
    {
        $orderCount = 10;
        $this->mockOrderCollectionFactory($orderCount);

        $mockCron = m::mock(
            OrderSyncStatus::class,
            [
                $this->orderCollectionFactory,
                $this->orderRepository,
                $this->scopeConfig,
                $this->syncStatus,
                $this->configWriter,
                $this->config,
                $this->cacheTypeList,
                $this->cacheFrontendPool
            ]
        )->makePartial();

        $orders = $mockCron->getOrderIds();
        $this->assertEquals(count($orders), $orderCount);
        $this->assertArrayHasKey('entity_id', $orders[0]);
    }

    public function mockOrderCollectionFactory($order_count)
    {
        $this->orderCollectionFactory->shouldReceive('create')
            ->once()
            ->andReturn($this->orderCollectionFactory);

        $this->orderCollectionFactory->shouldReceive('addAttributeToSort')
            ->once()
            ->with('entity_id', 'desc')
            ->andReturn($this->orderCollectionFactory);

        $this->orderCollectionFactory->shouldReceive('setPageSize')
            ->once()
            ->andReturn($this->orderCollectionFactory);

        $this->orderCollectionFactory->shouldReceive('setCurPage')
            ->once()
            ->with(1)
            ->andReturn($this->orderCollectionFactory);

        $this->orderCollectionFactory->shouldReceive('getSelect')
            ->once()
            ->andReturn($this->orderCollectionFactory);

        $this->orderCollectionFactory->shouldReceive('join')
            ->once()
            ->andReturn($this->orderCollectionFactory);

        $this->orderCollectionFactory->shouldReceive('getTable')
            ->once()
            ->andReturn($this->orderCollectionFactory);

        $this->orderCollectionFactory->shouldReceive('where')
            ->times(2)
            ->andReturn($this->orderCollectionFactory);

        $this->orderCollectionFactory->shouldReceive('getData')
            ->once()
            ->andReturn($this->getOrderIdsMock($order_count));
    }

    public function mockScopeConfig()
    {
        $this->scopeConfig->shouldReceive('getValue')
            ->once()
            ->with('payment/omise/cron_last_order_id')
            ->andReturn(0);
    }

    public function mockOrder($times, $conditional_execution_times)
    {
        // mock for $this->order->getStore()->getId()
        $orderMock = m::mock(stdClass::class);

        $orderMock->shouldReceive('getStore')
            ->times($times)
            ->andReturn($orderMock);

        $orderMock->shouldReceive('getId')
            ->times($times)
            ->andReturn(1);

        $this->mockOrderPayment($orderMock, $times);

        // these functions are called only if charge is expired.
        $orderMock->shouldReceive('registerCancellation')
            ->times($conditional_execution_times)
            ->andReturn($orderMock);

        $orderMock->shouldReceive('save')
            ->times($conditional_execution_times);

        return $orderMock;
    }

    public function mockOrderPayment(&$orderMock, $times)
    {
        $orderMock->shouldReceive('getPayment')
            ->times($times)
            ->andReturn($orderMock);

        $orderMock->shouldReceive('getAdditionalInformation')
            ->times($times)
            ->andReturn('charge_id_124');
    }

    public function mockOrderRepository($times, $orderMock)
    {
        $this->orderRepository->shouldReceive('get')
            ->times($times)
            ->andReturn($orderMock);
    }

    public function mockConfig($times)
    {
        $this->config->shouldReceive('getValue')
            ->once()
            ->with('enable_cron_autoexpirysync')
            ->andReturn(true);

        $this->config->shouldReceive('setStoreId')
            ->times($times);

        $this->mockConfigPublicKeyAndPrivateKey($times);
    }

    public function mockConfigPublicKeyAndPrivateKey($times)
    {
        $this->config->shouldReceive('getPublicKey')
            ->times($times)
            ->andReturn('public_key_123');

        $this->config->shouldReceive('getSecretKey')
            ->times($times)
            ->andReturn('secret_key_123');
    }

    public function mockOmiseCharge($times, $is_expired)
    {
        $omiseChargeMock = m::mock('overload:OmiseCharge');
        $omiseChargeMock->shouldReceive('retrieve')
            ->times($times)
            ->andReturn(['expired' => $is_expired]);

        return $omiseChargeMock;
    }

    public function mockSyncStatus($times, $orderMock)
    {
        $this->syncStatus->shouldReceive('cancelOrderInvoice')
            ->times($times)
            ->with($orderMock);
    }
}
