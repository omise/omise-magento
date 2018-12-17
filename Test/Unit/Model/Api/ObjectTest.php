<?php

namespace Omise\Payment\Test\Unit\Model\Api;

use PHPUnit\Framework\TestCase;
use Omise\Payment\Model\Api\BaseObject;

class ObjectTest extends TestCase
{
    /**
     * @test
     */
    public function refresh_object()
    {
        $object = (new MockObject)->execute(['id' => 'chrg_test_1', 'status' => 'successful']);

        $this->assertInstanceOf(MockObject::class, $object);
        $this->assertEquals('chrg_test_1', $object->id);
        $this->assertEquals('successful', $object->status);
    }

    /**
     * @test
     */
    public function refresh_object_twice()
    {
        $object = (new MockObject)->execute(['id' => 'chrg_test_1', 'status' => 'successful']);
        $object = $object->execute(['id' => 'chrg_test_2', 'status' => 'failed']);

        $this->assertInstanceOf(MockObject::class, $object);
        $this->assertEquals('chrg_test_2', $object->id);
        $this->assertEquals('failed', $object->status);
    }

    /**
     * @test
     *
     * This is a special case, made for Omise-PHP library object.
     */
    public function refresh_omise_object()
    {
        $object = (new MockObject)->execute((new MockOmiseObject));
        $object->reload();

        $this->assertInstanceOf(MockObject::class, $object);
        $this->assertEquals('chrg_test_3', $object->id);
        $this->assertEquals('pending', $object->status);
    }

    /**
     * @test
     */
    public function refresh_object_with_empty_data()
    {
        $object = (new MockObject)->execute();

        $this->assertInstanceOf(MockObject::class, $object);
    }

    /**
     * @test
     */
    public function trying_to_access_undefined_property_should_return_empty_string()
    {
        $object = (new MockObject)->execute();

        $this->assertEquals('', $object->undefined);
    }
}

/**
 * Mock class
 */
class MockObject extends Object
{
    public function execute($fake_data = null)
    {
        $this->refresh($fake_data);

        return $this;
    }

    public function reload()
    {
        $this->refresh();

        return $this;
    }
}

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
