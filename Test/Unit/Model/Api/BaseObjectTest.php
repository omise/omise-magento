<?php

namespace Omise\Payment\Test\Unit\Model\Api;

use Omise\Payment\Model\Api\BaseObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class BaseObjectTest extends TestCase
{
    // Helper class to expose protected method
    public function getBaseObject()
    {
        return new class extends BaseObject {
            public function callRefresh($object = null)
            {
                return $this->refresh($object);
            }
        };
    }

    /**
     * @covers \Omise\Payment\Model\Api\BaseObject::refresh
     * @covers \Omise\Payment\Model\Api\BaseObject::__get
     */
    public function testRefreshWithNullObject()
    {
        $baseObject = $this->getBaseObject();

        $result = $baseObject->callRefresh(); // use wrapper
        $this->assertSame($baseObject, $result);
    }

    /**
     * @covers \Omise\Payment\Model\Api\BaseObject::refresh
     * @covers \Omise\Payment\Model\Api\BaseObject::__get
     */
    public function testRefreshWithNewObject()
    {
        $baseObject = $this->getBaseObject();

        $newObject = ['key' => 'value'];
        $result = $baseObject->callRefresh($newObject);

        $this->assertSame($baseObject, $result);
        $this->assertEquals('value', $baseObject->key); // __get is called here
    }

    /**
     * @covers \Omise\Payment\Model\Api\BaseObject::refresh
     */
    public function testRefreshCallsObjectRefreshMethod()
    {
        $mockObject = $this->getMockBuilder(stdClass::class)
            ->addMethods(['refresh'])
            ->getMock();

        $mockObject->expects($this->once())->method('refresh');

        $baseObject = $this->getBaseObject();

        // inject object
        $reflection = new \ReflectionClass($baseObject);
        $property = $reflection->getProperty('object');
        $property->setAccessible(true);
        $property->setValue($baseObject, $mockObject);

        $baseObject->callRefresh(); // use wrapper
    }

    /**
     * @covers \Omise\Payment\Model\Api\BaseObject::__get
     * @covers \Omise\Payment\Model\Api\BaseObject::refresh
     */
    public function testGetExistingAndNonExistingKey()
    {
        $baseObject = $this->getBaseObject();

        $object = ['foo' => 'bar'];
        $baseObject->callRefresh($object);

        $this->assertEquals('bar', $baseObject->foo); // existing key
        $this->assertNull($baseObject->nonExisting);  // non-existing key
    }
}