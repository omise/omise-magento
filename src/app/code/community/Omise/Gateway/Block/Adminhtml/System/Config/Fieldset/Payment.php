<?php
class Omise_Gateway_Block_Adminhtml_System_Config_Fieldset_Payment extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * Return header title part of html for fieldset.
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     *
     * @see    Mage_Adminhtml_Block_System_Config_Form_Fieldset::_getHeaderTitleHtml()
     */
    protected function _getHeaderTitleHtml($element)
    {
        $html  = '<div class="config-heading entry-edit-head collapseable">';
        $html .= '  <a  id="' . $element->getHtmlId() . '-head"
                        href="#"
                        onclick="Fieldset.toggleCollapse(\'' . $element->getHtmlId() . '\', \'' . $this->getUrl('*/*/state') . '\'); return false;">';
        $html .=        $element->getLegend();
        $html .= '  </a>';

        if ($element->getComment()) {
            $html .= '<span class="heading-intro">' . $element->getComment() . '</span>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Return header comment part of html for fieldset.
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     *
     * @see    Mage_Adminhtml_Block_System_Config_Form_Fieldset::_getHeaderCommentHtml()
     */
    protected function _getHeaderCommentHtml($element)
    {
        return '';
    }
}
