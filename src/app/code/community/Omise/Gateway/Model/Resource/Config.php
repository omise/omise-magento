<?php
class Omise_Gateway_Model_Resource_Config extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('omise_gateway/omise', 'id');
        $this->_isPkAutoIncrement = false;
    }
}