<?php
class Omise_Gateway_Model_Resource_Config extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Note:
     * Here is an internal constructor (different from real __construct() one).
     * (It's recommened by Magento to override this method instead of __construct()).
     *
     * @see Magento: Mage/Core/Model/Resource/Abstract
     */
    protected function _construct()
    {
        $this->_init('omise_gateway/omise', 'id');
        $this->_isPkAutoIncrement = false;
    }
}
