<?php

namespace Omise\Payment\Test\Unit\Model\Api;

use PHPUnit\Framework\TestCase;
use Omise\Payment\Model\Api\Capability;
use Omise\Payment\Model\Config\Config;
use Mockery as m;

class CapabilityTest extends TestCase
{
    private $configMock;
    private $omiseCapabilityMock;

    protected function setUp(): void
    {
        $this->configMock = m::mock(Config::class);
        $this->configMock->shouldReceive('canInitialize')->andReturn(true);
        $this->omiseCapabilityMock = m::mock('alias:OmiseCapability');
    }

    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @covers Omise\Payment\Model\Api\Capability
     */
    public function testGetInstallmentMinLimit()
    {
        $data = [
            'limits' => [
                'installment_amount' => [
                    'min' => 3000
                ]
            ]
        ];
        $this->omiseCapabilityMock->shouldReceive('retrieve')->andReturn($data);
        $capability = new Capability($this->configMock);
        $result = $capability->getInstallmentMinLimit();

        $this->assertEquals($data['limits']['installment_amount']['min'], $result);
    }

    /**
     * @covers Omise\Payment\Model\Api\Capability
     */
    public function testGetTokenizationMethods()
    {
        $data = [
            'tokenization_methods' => [
                'googlepay',
                'applepay'
            ]
        ];
        $this->omiseCapabilityMock->shouldReceive('retrieve')->andReturn($data);
        $capability = new Capability($this->configMock);
        $result = $capability->getTokenizationMethods();

        $this->assertEquals($data['tokenization_methods'], $result);
    }

    /**
     * @covers Omise\Payment\Model\Api\Capability
     */
    public function testIsZeroInterest()
    {
        $data = [
            'zero_interest_installments' => 0
        ];
        $this->omiseCapabilityMock->shouldReceive('retrieve')->andReturn($data);
        $capability = new Capability($this->configMock);
        $result = $capability->isZeroInterest();

        $this->assertEquals($data['zero_interest_installments'], $result);
    }

    /**
     * @covers Omise\Payment\Model\Api\Capability
     */
    public function testGetInstallmentMinLimitReturnsZeroIfCapabilityIsNotSet()
    {
        $this->omiseCapabilityMock->shouldReceive('retrieve')->andReturn(null);
        $capability = new Capability($this->configMock);
        $result = $capability->getInstallmentMinLimit();

        $this->assertEquals(0, $result);
    }
}
