<?php

namespace Omise\Payment\Test\Unit\Gateway\Http\Client;

use Exception;
use Omise\Payment\Gateway\Http\Client\APMSession;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Model\Api\Charge as ApiCharge;
use Omise\Payment\Model\Omise;
use PHPUnit\Framework\TestCase;

class APMSessionTest extends TestCase
{
    /**
     * @var APMSession|\PHPUnit\Framework\MockObject\MockObject
     */
    private $model;

    /**
     * @var ApiCharge|\PHPUnit\Framework\MockObject\MockObject
     */
    private $apiCharge;

    /**
     * @var Omise|\PHPUnit\Framework\MockObject\MockObject
     */
    private $omise;

    /**
     * @var OmiseHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $omiseHelper;

    protected function setUp(): void
    {
        $this->apiCharge = $this->createMock(ApiCharge::class);

        $this->omise = $this->createMock(Omise::class);

        $this->omiseHelper = $this->createMock(OmiseHelper::class);

        $this->model = $this->getMockBuilder(APMSession::class)
            ->setConstructorArgs([
                $this->apiCharge,
                $this->omise,
                $this->omiseHelper
            ])
            ->onlyMethods(['execute'])
            ->getMock();
    }

    public function testCreateSessionSuccess()
    {
        $response = [
            'object' => 'session',
            'id' => 'sess_test_123'
        ];

        $this->model->expects($this->once())
            ->method('execute')
            ->willReturn(json_encode($response));

        $result = $this->model->createSession(
            'https://example.com',
            'POST',
            'skey_test',
            ['amount' => 100],
            true
        );

        $this->assertEquals($response, $result);
    }

    public function testCreateSessionThrowsExceptionForInvalidResponse()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown error. (Bad Response)');

        $this->model->expects($this->once())
            ->method('execute')
            ->willReturn('invalid-json');

        $this->model->createSession(
            'https://example.com',
            'POST',
            'skey_test'
        );
    }

    public function testCreateSessionThrowsOmiseException()
    {
        $response = [
            'object' => 'error',
            'message' => 'something went wrong'
        ];

        $this->expectException(\OmiseException::class);

        $this->model->expects($this->once())
            ->method('execute')
            ->willReturn(json_encode($response));

        $this->model->createSession(
            'https://example.com',
            'POST',
            'skey_test'
        );
    }

    public function testIsValidApiResponse()
    {
        $reflection = new \ReflectionClass(APMSession::class);

        $method = $reflection->getMethod('isValidAPIResponse');

        $method->setAccessible(true);

        $valid = $method->invoke(null, [
            'object' => 'session'
        ]);

        $invalid = $method->invoke(null, []);

        $this->assertTrue($valid);

        $this->assertFalse($invalid);
    }
}