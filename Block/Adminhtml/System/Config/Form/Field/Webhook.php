<?php

namespace Omise\Payment\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Url;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Webhook extends Field
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
        Context $context,
        Url     $urlHelper,
        array   $data = []
    ) {
        $this->urlHelper = $urlHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->urlHelper->getRouteUrl('omise/callback/webhook', ['_secure' => true]);
    }
}
