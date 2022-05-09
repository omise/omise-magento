<?php

namespace Omise\Payment\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http;

class Webhook extends Field
{
    /**
     * URL for omise webhook.
     */
    const URI = 'omise/callback/webhook';

    /**
     * @var \Magento\Framework\Url
     */
    protected $urlHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Request\Http $http
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Http $request,
        array   $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    /**
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        // fetch the store ID from the URL. The value will be NULL if the storeview is a default view.
        $storeId = $this->request->getParam('website') ?? $this->request->getParam('store');

        // if $storeId is not null then we are in the sub-store view.
        // We want to set the current store so that we get desired base URL
        if ($storeId) {
            $this->storeManager->setCurrentStore($storeId);
        }

        return $this->storeManager->getStore()->getBaseUrl() . self::URI;
    }
}
