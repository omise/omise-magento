<?php
declare(strict_types=1);

namespace Omise\Payment\Test\Unit\Block\Adminhtml\System\Config\Form\Field;

use Omise\Payment\Block\Adminhtml\System\Config\Form\Field\Webhook;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Block\Template\Context;

/**
 * @covers \Omise\Payment\Block\Adminhtml\System\Config\Form\Field\Webhook
 */
class WebhookTest extends TestCase
{
    /** @var Webhook */
    private $block;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var HttpRequest */
    private $request;

    /** @var AbstractElement */
    private $element;

    /** @var Store */
    private $store;

    protected function setUp(): void
    {
        // Mock Store
        $this->store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBaseUrl'])
            ->getMock();

        // Mock StoreManager
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStore', 'setCurrentStore'])
            ->getMockForAbstractClass();
        $this->storeManager->method('getStore')->willReturn($this->store);

        // Mock Request
        $this->request = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getParam'])
            ->getMock();

        // Mock Context
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Instantiate the Webhook block
        $this->block = new Webhook($context, $this->storeManager, $this->request);

        // Mock AbstractElement
        $this->element = $this->getMockBuilder(AbstractElement::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\Form\Field\Webhook::_getElementHtml
     */
    public function testGetElementHtmlDefaultStore(): void
    {
        // No website/store parameters → default store
        $this->request->method('getParam')->willReturn(null);
        $this->store->method('getBaseUrl')->willReturn('https://default.store/');

        // Call protected method via reflection
        $method = new \ReflectionMethod($this->block, '_getElementHtml');
        $method->setAccessible(true);
        $url = $method->invoke($this->block, $this->element);

        $this->assertSame('https://default.store/omise/callback/webhook', $url);
    }

    /**
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\Form\Field\Webhook::_getElementHtml
     */
    public function testGetElementHtmlWithStoreId(): void
    {
        // Return null for 'website', 2 for 'store'
        $this->request->method('getParam')->willReturnCallback(function ($param) {
            return $param === 'store' ? 2 : null;
        });

        // Expect setCurrentStore to be called once with 2
        $this->storeManager->expects($this->once())
            ->method('setCurrentStore')
            ->with(2);

        $this->store->method('getBaseUrl')->willReturn('https://store2.example.com/');

        // Call protected method via reflection
        $method = new \ReflectionMethod($this->block, '_getElementHtml');
        $method->setAccessible(true);
        $url = $method->invoke($this->block, $this->element);

        $this->assertSame('https://store2.example.com/omise/callback/webhook', $url);
    }
}
