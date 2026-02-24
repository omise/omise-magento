<?php

namespace Omise\Payment\Test\Unit\Model\Api;

use PHPUnit\Framework\TestCase;
use Omise\Payment\Model\Api\Capabilities;
use Omise\Payment\Model\Config\Config;
use Mockery as m;

class CapabilitiesTest extends TestCase
{
    private $configMock;
    private $omiseCapabilitiesMock;

    protected function setUp(): void
    {
        $this->configMock = m::mock(Config::class);
        $this->configMock->shouldReceive('canInitialize')->andReturn(true);
        $this->omiseCapabilitiesMock = m::mock('alias:OmiseCapabilities');
    }

    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @covers Omise\Payment\Model\Api\Capabilities
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
        $this->omiseCapabilitiesMock->shouldReceive('retrieve')->andReturn($data);
        $capabilities = new Capabilities($this->configMock);
        $result = $capabilities->getInstallmentMinLimit();

        $this->assertEquals($data['limits']['installment_amount']['min'], $result);
    }

    /**
     * @covers Omise\Payment\Model\Api\Capabilities
     */
    public function testGetInstallmentMinLimitReturnsZeroIfCapabilitiesIsNotSet()
    {
        $this->omiseCapabilitiesMock->shouldReceive('retrieve')->andReturn(null);
        $capabilities = new Capabilities($this->configMock);
        $result = $capabilities->getInstallmentMinLimit();

        $this->assertEquals(0, $result);
    }
}
