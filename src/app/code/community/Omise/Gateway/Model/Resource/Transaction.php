<?php
class Omise_Gateway_Model_Resource_Transaction extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('omise_gateway/transaction', 'id');
        $this->_isPkAutoIncrement = true;
    }
}