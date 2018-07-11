<?php
namespace Omise\Payment\Block\Adminhtml\System\Config\Fieldset;

/**
 * Fieldset renderer for Omise solution
 */
class Payment extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * Add custom css class
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getFrontendClass($element)
    {
        return parent::_getFrontendClass($element) . ' with-button enabled';
    }

    /**
     * Return header title part of html for payment solution
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getHeaderTitleHtml($element)
    {
        $htmlId = $element->getHtmlId();

        return
            '<div class="config-heading">
                <div class="button-container">
                    <button type="button"
                            class="button action-configure"
                            id="' . $htmlId . '-head"
                            onclick="omiseButtonToggle.call(this, \'' . $htmlId . "', '" . $this->getUrl('adminhtml/*/state') . '\'); return false;">
                        <span class="state-closed">' . __('Configure') . '</span>
                        <span class="state-opened">' . __('Close') . '</span>
                    </button>
                </div>
                <div class="heading">
                    <strong>' . $element->getLegend() . '</strong>
                    <span class="heading-intro">' .
                        __('Accept credit/debit cards and Alipay and Internet Banking Payments in your Magento store') . '<br/>
                    </span>
                    <div class="config-alt"></div>
                </div>
            </div>';
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getExtraJs($element)
    {
        $script = "require(['jquery', 'prototype'], function(jQuery){
            window.omiseButtonToggle = function (id, url) {
                var doScroll = false;
                Fieldset.toggleCollapse(id, url);
                if ($(this).hasClassName(\"open\")) {
                    $$(\".with-button button.button\").each(function(anotherButton) {
                        if (anotherButton != this && $(anotherButton).hasClassName(\"open\")) {
                            $(anotherButton).click();
                            doScroll = true;
                        }
                    }.bind(this));
                }
                if (doScroll) {
                    var pos = Element.cumulativeOffset($(this));
                    window.scrollTo(pos[0], pos[1] - 45);
                }
            }
        });";

        return $this->_jsHelper->getScript($script);
    }
}
