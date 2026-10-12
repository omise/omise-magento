<?php

namespace Omise\Payment\Test\Unit\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\ScopeInterface;
use Omise\Payment\Block\Adminhtml\System\Config\Form\Field\UpaWebhookSetting;
use PHPUnit\Framework\TestCase;

class UpaWebhookSettingTest extends TestCase
{
    /**
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\Form\Field\UpaWebhookSetting::__construct
     * @uses \Magento\Config\Block\System\Config\Form\Field::__construct
     * @uses \Magento\Backend\Block\Template::__construct
     */
    public function testConstructorRunsBeforeMagentoParentRequiresObjectManager(): void
    {
        $context = $this->createMock(Context::class);
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("ObjectManager isn't initialized");

        new UpaWebhookSetting($context, $scopeConfig);
    }

    /**
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\Form\Field\UpaWebhookSetting::_getElementHtml
     * @uses \Magento\Config\Block\System\Config\Form\Field::_getElementHtml
     */
    public function testRenderAtStoreScopeWithFeatureEnabled(): void
    {
        $this->assertRenderedSetting(
            ['store' => 'store_2', 'website' => 'website_1'],
            ScopeInterface::SCOPE_STORE,
            'store_2',
            true,
            true
        );
    }

    /**
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\Form\Field\UpaWebhookSetting::_getElementHtml
     * @uses \Magento\Config\Block\System\Config\Form\Field::_getElementHtml
     */
    public function testRenderAtWebsiteScopeWithFeatureEnabled(): void
    {
        $this->assertRenderedSetting(
            ['website' => 'website_1'],
            ScopeInterface::SCOPE_WEBSITE,
            'website_1',
            true,
            true
        );
    }

    /**
     * @covers \Omise\Payment\Block\Adminhtml\System\Config\Form\Field\UpaWebhookSetting::_getElementHtml
     * @uses \Magento\Config\Block\System\Config\Form\Field::_getElementHtml
     */
    public function testRenderAtDefaultScopeWithFeatureDisabled(): void
    {
        $this->assertRenderedSetting([], ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, false, false);
    }

    /**
     * @param array $params
     * @param string $scope
     * @param string|null $scopeId
     * @param bool $enabled
     * @param bool $addsHiddenInput
     */
    private function assertRenderedSetting(
        array $params,
        string $scope,
        ?string $scopeId,
        bool $enabled,
        bool $addsHiddenInput
    ): void {
        $request = $this->createMock(RequestInterface::class);
        $request->method('getParam')->willReturnCallback(
            function ($key) use ($params) {
                return $params[$key] ?? null;
            }
        );

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with('payment/omise/is_upa_feature_flag_enabled', $scope, $scopeId)
            ->willReturn($enabled);

        $block = (new \ReflectionClass(TestableUpaWebhookSetting::class))
            ->newInstanceWithoutConstructor();
        $block->setRequestForTest($request);
        $scopeConfigProperty = new \ReflectionProperty(UpaWebhookSetting::class, 'scopeConfig');
        $scopeConfigProperty->setAccessible(true);
        $scopeConfigProperty->setValue($block, $scopeConfig);
        $element = $this->getMockBuilder(AbstractElement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getElementHtml', 'getName'])
            ->addMethods(['setValue', 'setDisabled'])
            ->getMock();
        $element->method('getElementHtml')->willReturn('<input id="upa_webhook" />');
        $element->method('getName')->willReturn('groups[omise][fields][webhook_status][value]');

        if ($enabled) {
            $element->expects($this->once())->method('setValue')->with('1');
            $element->expects($this->once())->method('setDisabled')->with(true);
        } else {
            $element->expects($this->never())->method('setValue');
            $element->expects($this->never())->method('setDisabled');
        }

        $html = $block->renderElementHtml($element);

        $this->assertStringContainsString(
            '<div data-mage-init="{&quot;Omise_Payment/js/upa-webhook-setting&quot;:{}}">',
            $html
        );
        $this->assertStringContainsString('<input id="upa_webhook" />', $html);
        if ($addsHiddenInput) {
            $this->assertStringContainsString(
                '<input type="hidden" name="groups[omise][fields][webhook_status][value]" value="1" />',
                $html
            );
        } else {
            $this->assertStringNotContainsString('type="hidden"', $html);
        }
    }
}

/**
 * Exposes the protected rendering method and request property for unit tests.
 */
class TestableUpaWebhookSetting extends UpaWebhookSetting
{
    public function setRequestForTest(RequestInterface $request): void
    {
        $this->_request = $request;
    }

    public function renderElementHtml(AbstractElement $element): string
    {
        return $this->_getElementHtml($element);
    }
}
