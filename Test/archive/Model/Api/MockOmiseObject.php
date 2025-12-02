<?php

namespace Omise\Payment\Test\Unit\Model\Api;

/**
 * Mock Omise object class.
 * Usually OmiseObject will be extended by Omise resource classes (i.e. OmiseCharge, OmiseCustomer and so on).
 * Here is to mock that object so we can test 'refresh' its object into \Model\Api\BaseObject.
 *
 * @see refresh_omise_object  method of this test (it will be used only in that test).
 */
class MockOmiseObject implements \ArrayAccess
{
    // Store the attributes of the object.
    protected $_values = [];

    public function refresh()
    {
        $this->_values = [
            'id'     => 'chrg_test_3',
            'status' => 'pending'
        ];
    }

    /**
     * Override methods of ArrayAccess
     */
    public function offsetSet($key, $value)
    {
        $this->_values[$key] = $value;
    }

    public function offsetExists($key)
    {
        return isset($this->_values[$key]);
    }

    public function offsetUnset($key)
    {
        unset($this->_values[$key]);
    }

    public function offsetGet($key)
    {
        return isset($this->_values[$key]) ? $this->_values[$key] : null;
    }
}
