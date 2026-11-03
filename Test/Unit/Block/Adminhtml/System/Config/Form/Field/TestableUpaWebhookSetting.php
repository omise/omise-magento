<?php

namespace Omise\Payment\Test\Unit\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Omise\Payment\Block\Adminhtml\System\Config\Form\Field\UpaWebhookSetting;

/**
 * Exposes protected members for UpaWebhookSetting unit tests.
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
