<?php

namespace Omise\Payment\Test\Unit\Controller\Adminhtml\Ordersync;

use Omise\Payment\Controller\Adminhtml\Ordersync\Index as OrderSyncController;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Omise\Payment\Helper\OmiseHelper;
use Psr\Log\LoggerInterface;
use Omise\Payment\Model\SyncStatus;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

class IndexTest extends TestCase
{
    private $context;
    private $registry;
    private $orderRepository;
    private $orderManagement;
    private $helper;
    private $logger;
    private $syncStatus;
    private $messageManager;
    private $resultRedirectFactory;
    private $controller;
    private $requestMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->requestMock->method('getParam')->with('order_id')->willReturn(1);

        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->resultRedirectFactory = $this->createMock(RedirectFactory::class);

        $this->context = $this->createMock(Context::class);
        $this->context->method('getRequest')->willReturn($this->requestMock);
        $this->context->method('getMessageManager')->willReturn($this->messageManager);
        $this->context->method('getResultRedirectFactory')->willReturn($this->resultRedirectFactory);

        $this->registry = $this->createMock(Registry::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->orderManagement = $this->createMock(OrderManagementInterface::class);
        $this->helper = $this->createMock(OmiseHelper::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->syncStatus = $this->createMock(SyncStatus::class);

        $this->controller = new OrderSyncController(
            $this->context,
            $this->registry,
            $this->orderManagement,
            $this->orderRepository,
            $this->helper,
            $this->logger,
            $this->syncStatus
        );

        // Set protected _actionFlag
        $mockActionFlag = new class {
            public function set($controller, $flag, $value) 
            { 
                return true; 
            }
        };
        $reflection = new \ReflectionProperty($this->controller, '_actionFlag');
        $reflection->setAccessible(true);
        $reflection->setValue($this->controller, $mockActionFlag);
    }

    /**
     * @covers \Omise\Payment\Controller\Adminhtml\Ordersync\Index::__construct
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(OrderSyncController::class, $this->controller);
    }

    /**
     * @covers \Omise\Payment\Controller\Adminhtml\Ordersync\Index::__construct
     * @covers \Omise\Payment\Controller\Adminhtml\Ordersync\Index::_initOrder
     */
    public function testInitOrderReturnsOrder(): void
    {
        $order = $this->createMock(OrderInterface::class);

        $this->orderRepository->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn($order);

        $this->registry->expects($this->exactly(2))
            ->method('register')
            ->withConsecutive(['sales_order', $order], ['current_order', $order]);

        $reflection = new \ReflectionMethod($this->controller, '_initOrder');
        $reflection->setAccessible(true);
        $result = $reflection->invoke($this->controller);

        $this->assertSame($order, $result);
    }

    /**
     * @covers \Omise\Payment\Controller\Adminhtml\Ordersync\Index::__construct
     * @covers \Omise\Payment\Controller\Adminhtml\Ordersync\Index::_initOrder
     * @dataProvider exceptionProvider
     */
    public function testInitOrderHandlesException($exceptionClass): void
    {
        $this->orderRepository->method('get')->willThrowException(new $exceptionClass());
        $this->messageManager->expects($this->once())->method('addErrorMessage');

        $reflection = new \ReflectionMethod($this->controller, '_initOrder');
        $reflection->setAccessible(true);
        $result = $reflection->invoke($this->controller);

        $this->assertFalse($result);
    }

    /**
     * @covers \Omise\Payment\Controller\Adminhtml\Ordersync\Index::__construct
     * @covers \Omise\Payment\Controller\Adminhtml\Ordersync\Index::execute
     */
    public function testExecuteOrderExistsSyncSuccess(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getEntityId')->willReturn(123);

        $controller = $this->getMockBuilder(OrderSyncController::class)
            ->onlyMethods(['_initOrder'])
            ->setConstructorArgs([
                $this->context,
                $this->registry,
                $this->orderManagement,
                $this->orderRepository,
                $this->helper,
                $this->logger,
                $this->syncStatus
            ])->getMock();

        $controller->method('_initOrder')->willReturn($order);

        $this->syncStatus->expects($this->once())->method('sync')->with($order);
        $this->messageManager->expects($this->once())->method('addSuccessMessage');

        $redirect = $this->createMock(Redirect::class);
        $this->resultRedirectFactory->method('create')->willReturn($redirect);
        $redirect->expects($this->once())->method('setPath')->with('sales/order/view', ['order_id' => 123]);

        $controller->execute();
    }

    /**
     * @covers \Omise\Payment\Controller\Adminhtml\Ordersync\Index::__construct
     * @covers \Omise\Payment\Controller\Adminhtml\Ordersync\Index::execute
     */
    public function testExecuteOrderExistsSyncThrowsLocalizedException(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getEntityId')->willReturn(123);

        $controller = $this->getMockBuilder(OrderSyncController::class)
            ->onlyMethods(['_initOrder'])
            ->setConstructorArgs([
                $this->context,
                $this->registry,
                $this->orderManagement,
                $this->orderRepository,
                $this->helper,
                $this->logger,
                $this->syncStatus
            ])->getMock();

        $controller->method('_initOrder')->willReturn($order);

        $exception = new LocalizedException(__('Sync error'));
        $this->syncStatus->expects($this->once())->method('sync')->with($order)->willThrowException($exception);
        $this->messageManager->expects($this->once())->method('addErrorMessage')->with('Sync error');

        $redirect = $this->createMock(Redirect::class);
        $this->resultRedirectFactory->method('create')->willReturn($redirect);
        $redirect->expects($this->once())->method('setPath')->with('sales/order/view', ['order_id' => 123]);

        $controller->execute();
    }

    /**
     * @covers \Omise\Payment\Controller\Adminhtml\Ordersync\Index::__construct
     * @covers \Omise\Payment\Controller\Adminhtml\Ordersync\Index::execute
     */
    public function testExecuteOrderDoesNotExist(): void
    {
        $controller = $this->getMockBuilder(OrderSyncController::class)
            ->onlyMethods(['_initOrder'])
            ->setConstructorArgs([
                $this->context,
                $this->registry,
                $this->orderManagement,
                $this->orderRepository,
                $this->helper,
                $this->logger,
                $this->syncStatus
            ])->getMock();

        $controller->method('_initOrder')->willReturn(false);

        $redirect = $this->createMock(Redirect::class);
        $this->resultRedirectFactory->method('create')->willReturn($redirect);
        $redirect->expects($this->once())->method('setPath')->with('sales/*/');

        $controller->execute();
    }

    public static function exceptionProvider(): array
    {
        return [
            [NoSuchEntityException::class],
            [InputException::class],
        ];
    }
}
