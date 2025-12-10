<?php

namespace Omise\Payment\Test\Unit\Model\Api;

use ArrayAccess;

/**
 * Mock Omise object class.
 * Usually OmiseObject will be extended by Omise resource classes (i.e. OmiseCharge, OmiseCustomer and so on).
 * Here is to mock that object so we can test 'refresh' its object into \Model\Api\BaseObject.
 *
 * @see refresh_omise_object  method of this test (it will be used only in that test).
 */
#[\AllowDynamicProperties]
class MockOmiseObject implements ArrayAccess
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

    public function reload()
    {
        //
    }

    /**
     * Override methods of ArrayAccess
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->{$key} = $value;
    }

    public function offsetExists(mixed $key): bool
    {
        return isset($this->{$key});
    }

    public function offsetUnset(mixed $key): void
    {
        unset($this->{$key});
    }

    #[\ReturnTypeWillChange]
    public function offsetGet(mixed $key)
    {
        return isset($this->{$key}) ? $this->{$key} : null;
    }
}
