<?php
declare(strict_types=1);

namespace Omise\Payment\Test\Unit\Block\Adminhtml\System\Config\Fieldset;

use Omise\Payment\Block\Adminhtml\System\Config\Fieldset\Payment;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * @covers \Omise\Payment\Block\Adminhtml\System\Config\Fieldset\Payment
 */
class PaymentTest extends TestCase
{
    /** @var Payment */
    private $block;

    /** @var AbstractElement */
    private $element;

    /** @var object */
    private $jsHelper;

    protected function setUp(): void
    {
        // Partial mock of Payment block
        $this->block = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()   // avoid constructor DI issues
            ->onlyMethods(['getUrl'])        // override real method
            ->addMethods(['__'])             // magic translation method
            ->getMock();

        // Mock AbstractElement
        $this->element = $this->getMockBuilder(AbstractElement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHtmlId'])     // exists in AbstractElement
            ->addMethods(['getLegend'])      // does not exist in base class
            ->getMock();

        // Mock jsHelper as stdClass with getScript method
        $this->jsHelper = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getScript'])
            ->getMock();

        // Inject jsHelper into block
        $this->setProperty($this->block, '_jsHelper', $this->jsHelper);
    }

    /**
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\Fieldset\Payment::_getFrontendClass
     */
    public function testGetFrontendClassAppendsCss(): void
    {
        // Call protected method via reflection
        $method = new \ReflectionMethod($this->block, '_getFrontendClass');
        $method->setAccessible(true);
        $result = $method->invoke($this->block, $this->element);

        $this->assertStringContainsString('with-button enabled', $result);
    }

    /**
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\Fieldset\Payment::_getHeaderTitleHtml
     */
    public function testGetHeaderTitleHtmlGeneratesHtml(): void
    {
        $this->element->method('getHtmlId')->willReturn('fieldset_id');
        $this->element->method('getLegend')->willReturn('My Legend');

        $this->block->method('getUrl')->willReturn('http://dummy.url');
        $this->block->method('__')->willReturnArgument(0); // mock translation

        // Call protected method via reflection
        $method = new \ReflectionMethod($this->block, '_getHeaderTitleHtml');
        $method->setAccessible(true);
        $html = $method->invoke($this->block, $this->element);

        $this->assertStringContainsString('fieldset_id-head', $html);
        $this->assertStringContainsString('Configure', $html);
        $this->assertStringContainsString('Close', $html);
        $this->assertStringContainsString('My Legend', $html);
        $this->assertStringContainsString('http://dummy.url', $html);
    }

    /**
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\Fieldset\Payment::_getExtraJs
     */
    public function testGetExtraJsWrapsScript(): void
    {
        $this->element->method('getHtmlId')->willReturn('dummy_id');

        // Mock jsHelper to wrap script
        $this->jsHelper->method('getScript')->willReturnCallback(fn($script) => 'wrapped:' . $script);

        // Call protected method via reflection
        $method = new \ReflectionMethod($this->block, '_getExtraJs');
        $method->setAccessible(true);
        $result = $method->invoke($this->block, $this->element);

        $this->assertStringStartsWith('wrapped:', $result);
        $this->assertStringContainsString('require([\'jquery\', \'prototype\']', $result);
        $this->assertStringContainsString('window.omiseButtonToggle', $result);
    }

    /**
     * Helper to set private/protected property
     */
    private function setProperty(object $object, string $prop, $value): void
    {
        $ref = new \ReflectionProperty($object, $prop);
        $ref->setAccessible(true);
        $ref->setValue($object, $value);
    }
}
