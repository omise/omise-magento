<?php

namespace Omise\Payment\Test\Unit\Model\Api;

use Omise\Payment\Model\Api\Error;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Omise\Payment\Model\Api\Error
 */
class ErrorTest extends TestCase
{
    /**
     * Test default error values when no input is provided
     * @covers \Omise\Payment\Model\Api\Error::__construct
     * @covers \Omise\Payment\Model\Api\Error::getCode
     * @covers \Omise\Payment\Model\Api\Error::getMessage
     */
    public function testDefaultErrorValues()
    {
        $error = new Error();

        $this->assertSame('unexpected_error', $error->getCode());
        $this->assertSame(
            'There is an unexpected error happened, please contact our support for further investigation.',
            $error->getMessage()
        );
    }

    /**
     * Test constructor sets code and message if provided
     * @covers \Omise\Payment\Model\Api\Error::__construct
     * @covers \Omise\Payment\Model\Api\Error::setCode
     * @covers \Omise\Payment\Model\Api\Error::setMessage
     * @covers \Omise\Payment\Model\Api\Error::getCode
     * @covers \Omise\Payment\Model\Api\Error::getMessage
     */
    public function testConstructorSetsCodeAndMessage()
    {
        $data = [
            'code'    => 'invalid_request',
            'message' => 'This is a custom error message.'
        ];

        $error = new Error($data);

        $this->assertSame('invalid_request', $error->getCode());
        $this->assertSame('This is a custom error message.', $error->getMessage());
    }

    /**
     * Test individual setters
     * @covers \Omise\Payment\Model\Api\Error::setCode
     * @covers \Omise\Payment\Model\Api\Error::setMessage
     * @covers \Omise\Payment\Model\Api\Error::getCode
     * @covers \Omise\Payment\Model\Api\Error::getMessage
     */
    public function testSetters()
    {
        $error = new Error();

        $errorReflection = new \ReflectionClass($error);

        // setCode
        $setCodeMethod = $errorReflection->getMethod('setCode');
        $setCodeMethod->setAccessible(true);
        $setCodeMethod->invoke($error, 'new_code');

        $this->assertSame('new_code', $error->getCode());

        // setMessage
        $setMessageMethod = $errorReflection->getMethod('setMessage');
        $setMessageMethod->setAccessible(true);
        $setMessageMethod->invoke($error, 'New message');

        $this->assertSame('New message', $error->getMessage());
    }
}
