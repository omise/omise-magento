<?php
class Omise_Gateway_Model_Transaction extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('omise_gateway/transaction');
    }
}