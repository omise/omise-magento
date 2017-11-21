<?php

namespace Omise\Payment\Block\Adminhtml\System\Config\Form\Field;

class Webhook extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Framework\Url
     */
    protected $urlHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Url                  $urlHelper
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Url $urlHelper,
        array $data = []
    ) {
        $this->urlHelper = $urlHelper;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->urlHelper->getRouteUrl('omise/callback/webhook', ['_secure' => true]);
    }
}
