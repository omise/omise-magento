<?php

namespace Omise\Payment\Test\Unit\Model\Api;

use PHPUnit\Framework\TestCase;

/**
 * $covers \Omise\Payment\Model\Api\BaseObject
 */
class ObjectTest extends TestCase
{
    /**
     * @test
     */
    public function refreshObject()
    {
        $object = (new MockObject)->execute(['id' => 'chrg_test_1', 'status' => 'successful']);

        $this->assertInstanceOf(MockObject::class, $object);
        $this->assertEquals('chrg_test_1', $object->id);
        $this->assertEquals('successful', $object->status);
    }

    /**
     * @test
     */
    public function refreshObjectTwice()
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
    public function refreshOmiseObject()
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
    public function refreshObjectWithEmptyData()
    {
        $object = (new MockObject)->execute();

        $this->assertInstanceOf(MockObject::class, $object);
    }

    /**
     * @test
     */
    public function tryingToAccessUndefinedPropertyShouldReturnEmptyString()
    {
        $object = (new MockObject)->execute();

        $this->assertEquals('', $object->undefined);
    }
}
