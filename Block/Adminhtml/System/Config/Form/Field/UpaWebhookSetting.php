<?php

namespace Omise\Payment\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\ScopeInterface;

class UpaWebhookSetting extends Field
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $request = $this->getRequest();
        $website = $request->getParam('website');
        $store = $request->getParam('store');

        if ($store) {
            $scope = ScopeInterface::SCOPE_STORE;
            $scopeId = $store;
        } elseif ($website) {
            $scope = ScopeInterface::SCOPE_WEBSITE;
            $scopeId = $website;
        } else {
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
            $scopeId = null;
        }

        if ($this->scopeConfig->isSetFlag('payment/omise/is_upa_feature_flag_enabled', $scope, $scopeId)) {
            $element->setValue('1');
            $element->setDisabled(true);
            $html = parent::_getElementHtml($element)
                . '<input type="hidden" name="' . $element->getName() . '" value="1" />';
        } else {
            $html = parent::_getElementHtml($element);
        }

        return '<div data-mage-init="{&quot;Omise_Payment/js/upa-webhook-setting&quot;:{}}">'
            . $html . '</div>';
    }
}
