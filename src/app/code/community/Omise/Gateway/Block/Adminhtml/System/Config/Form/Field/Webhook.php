<?php
class Omise_Gateway_Block_Adminhtml_System_Config_Form_Field_Webhook extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return Mage::getUrl('omise/callback_webhook');
    }
}
