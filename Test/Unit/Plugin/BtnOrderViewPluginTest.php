<?php

namespace Omise\Payment\Test\Unit\Plugin;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Plugin\BtnOrderViewPlugin;
use Magento\Sales\Block\Adminhtml\Order\View as OrderViewBlock;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\TestCase;
use Mockery as m;

/**
 * @coversDefaultClass \Omise\Payment\Plugin\BtnOrderViewPlugin
 */
class BtnOrderViewPluginTest extends TestCase
{
    private $backendUrl;
    private $request;
    private $scopeConfig;
    private $helper;
    private $plugin;

    protected function setUp(): void
    {
        $this->backendUrl = m::mock(UrlInterface::class);
        $this->request = m::mock(Http::class);
        $this->scopeConfig = m::mock(ScopeConfigInterface::class);
        $this->helper = m::mock(OmiseHelper::class);

        $this->plugin = new BtnOrderViewPlugin(
            $this->backendUrl,
            $this->request,
            $this->scopeConfig,
            $this->helper
        );
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    /**
     * @covers ::__construct
     * @covers ::beforeSetLayout
     */
    public function testBeforeSetLayoutAddsButtonWhenAutoSyncEnabled(): void
    {
        $orderMock = m::mock(Order::class);
        $orderMock->shouldReceive('getId')->once()->andReturn(123);

        $subjectMock = m::mock(OrderViewBlock::class);
        $subjectMock->shouldReceive('getOrder')->once()->andReturn($orderMock);
        $subjectMock->shouldReceive('getRequest')->once()->andReturn($this->request);
        $this->request->shouldReceive('getParam')->once()->with('order_id')->andReturn(123);

        $this->helper->shouldReceive('canOrderStatusAutoSync')->once()->with($orderMock)->andReturn(true);

        // Allow the first call to backendUrl->getUrl() to be ignored
        $this->backendUrl->shouldReceive('getUrl')->zeroOrMoreTimes()->andReturn('ignored');

        $subjectMock->shouldReceive('getUrl')->once()->with(
            'omise/ordersync/',
            ['id' => 123]
        )->andReturn('generated_url');

        $subjectMock->shouldReceive('addButton')->once()->with(
            'sync_order_status',
            m::on(function ($arg) {
                return $arg['label'] instanceof \Magento\Framework\Phrase
                    && $arg['onclick'] === "setLocation('generated_url')"
                    && $arg['class'] === 'action-default scalable'
                    && $arg['title'] === 'Sync order status for omise payment methods';
            })
        );

        $result = $this->plugin->beforeSetLayout($subjectMock);
        $this->assertNull($result);
    }

    /**
     * @covers ::__construct
     * @covers ::beforeSetLayout
     */
    public function testBeforeSetLayoutDoesNothingWhenAutoSyncDisabled(): void
    {
        $orderMock = m::mock(Order::class);
        $subjectMock = m::mock(OrderViewBlock::class);
        $subjectMock->shouldReceive('getOrder')->once()->andReturn($orderMock);

        $this->helper->shouldReceive('canOrderStatusAutoSync')->once()->with($orderMock)->andReturn(false);

        $subjectMock->shouldReceive('addButton')->never();
        $subjectMock->shouldReceive('getUrl')->never();
        $this->request->shouldReceive('getParam')->never();
        $this->backendUrl->shouldReceive('getUrl')->zeroOrMoreTimes();

        $result = $this->plugin->beforeSetLayout($subjectMock);
        $this->assertNull($result);
    }
}
