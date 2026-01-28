<?php

namespace Omise\Payment\Test\Unit\Block\Adminhtml\System\Config\CardFormCustomization;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Filesystem\Driver\File;
use Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\FormModal;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Omise\Payment\Block\Adminhtml\System\Config\CardFormCustomization\FormModal
 */
class FormModalTest extends TestCase
{
    private FormModal $block;
    private AbstractElement $element;

    protected function setUp(): void
    {
        // Create block WITHOUT running Magento constructor
        $this->block = $this->getMockBuilder(FormModal::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_isInheritCheckboxRequired', '_getInheritCheckboxLabel'])
            ->getMock();

        // Inject fake filesystem
        $fs = $this->createMock(File::class);
        $fs->method('fileGetContents')->willReturn('FILE_CONTENT');

        $this->setProperty($this->block, 'localFileSystem', $fs);

        // Inject fake theme
        $theme = new class {
            public function getLightTheme() { return ['bg' => '#fff']; }
            public function getDarkTheme() { return ['bg' => '#000']; }
        };
        $this->setProperty($this->block, 'theme', $theme);

        $this->element = $this->getMockBuilder(AbstractElement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getName', 'getData'])
            ->getMock();
    }

    public function testGetScriptInjectsAllGlobalVariables(): void
    {
        $this->block->method('_isInheritCheckboxRequired')->willReturn(true);
        $this->block->method('_getInheritCheckboxLabel')->willReturn('Use Website');

        $this->element->method('getId')->willReturn('cc_design');
        $this->element->method('getName')->willReturn('groups[design][value]');
        $this->element->method('getData')->willReturnMap([
            ['value', 'dark'],
            ['inherit', 1],
        ]);

        $script = $this->block->getScript($this->element);

        $this->assertStringContainsString('window.OMISE_CC_INPUT_ID', $script);
        $this->assertStringContainsString('window.OMISE_CC_DESIGN', $script);
        $this->assertStringContainsString('window.OMISE_CC_LIGHT_THEME', $script);
        $this->assertStringContainsString('window.OMISE_CC_DARK_THEME', $script);
        $this->assertStringContainsString('window.OMISE_CC_INPUT_INHERIT_VALUE', $script);
        $this->assertStringContainsString('window.OMISE_CC_INPUT_INHERIT_LABEL', $script);
        $this->assertStringContainsString('window.OMISE_CC_INPUT_INHERIT_SHOULD_SHOW', $script);
    }

    public function testGetHtmlWithInheritCheckbox(): void
    {
        $this->block->method('_isInheritCheckboxRequired')->willReturn(true);

        $this->element->method('getId')->willReturn('design');
        $this->element->method('getName')->willReturn('groups[design][value]');
        $this->element->method('getData')->willReturnMap([
            ['value', 'dark'],
            ['inherit', 1],
        ]);

        $html = $this->block->getHtml($this->element);

        $this->assertStringContainsString('design_inherit', $html);
        $this->assertStringContainsString('FILE_CONTENT', $html);
    }

    public function testRenderOutputsCssHtmlAndJs(): void
    {
        $this->block->method('_isInheritCheckboxRequired')->willReturn(false);
        $this->block->method('_getInheritCheckboxLabel')->willReturn('');

        $this->element->method('getId')->willReturn('design');
        $this->element->method('getName')->willReturn('groups[design][value]');
        $this->element->method('getData')->willReturnMap([
            ['value', 'light'],
            ['inherit', 0],
        ]);

        $html = $this->block->render($this->element);

        $this->assertStringContainsString('<style>', $html);
        $this->assertStringContainsString('<script>', $html);
        $this->assertStringContainsString('FILE_CONTENT', $html);
    }

    private function setProperty(object $object, string $prop, $value): void
    {
        $ref = new \ReflectionProperty($object, $prop);
        $ref->setAccessible(true);
        $ref->setValue($object, $value);
    }
}