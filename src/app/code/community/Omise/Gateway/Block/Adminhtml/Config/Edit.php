<?php
class Omise_Gateway_Block_Adminhtml_Config_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Note:
     * Here is an internal constructor (different from real __construct() one).
     * (It's recommened by Magento to override this method instead of __construct()).
     *
     * @see Magento: Mage/Core/Block/Abstract
     */
    protected function _construct()
    {
        $this->_blockGroup = 'omise_gateway_adminhtml';
        $this->_controller = 'config';
        $this->_mode       = 'edit';
        $this->_headerText =  $this->__('Omise Gateway Configuration');
    }
}
