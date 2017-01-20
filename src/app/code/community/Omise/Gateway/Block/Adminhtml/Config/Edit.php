<?php
class Omise_Gateway_Block_Adminhtml_Config_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected function _construct()
    {
        $this->_blockGroup  = 'omise_gateway_adminhtml';
        $this->_controller  = 'config';
        $this->_mode        = 'edit';
        $this->_headerText  =  $this->__('Omise Gateway Configuration');
    }
}
