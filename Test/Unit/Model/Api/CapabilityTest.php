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

    /**
     * @covers Omise\Payment\Model\Api\Capability
     */
    public function testGetPaymentMethods()
    {
        $data = [
            'payment_methods' => [
                'name' => 'card',
                'name' => 'alipay'
            ]
        ];

        // Mock the object returned by OmiseCapability::retrieve()
        $omiseResult = m::mock();
        $omiseResult->shouldReceive('getPaymentMethods')
                    ->andReturn($data['payment_methods']);
        $omiseResult->shouldReceive('offsetGet')
                    ->with('payment_methods')
                    ->andReturn($data['payment_methods']);
        $omiseResult->shouldAllowMockingMethod('offsetGet');

        // Mock static alias OmiseCapability::retrieve()
        $this->omiseCapabilityMock
            ->shouldReceive('retrieve')
            ->andReturn($omiseResult);

        // Test the model
        $capability = new Capability($this->configMock);
        $result = $capability->getPaymentMethods();

        $this->assertEquals($data['payment_methods'], $result);
    }

    /**
     * @covers Omise\Payment\Model\Api\Capability
     */
    public function testGetInstallmentBackends()
    {
        $expected = ['installment'];
        // Mock OmiseCapability result
        $omiseResult = m::mock();
        // Required by Capability model
        $omiseResult->shouldReceive('getInstallmentBackends')
                    ->andReturn($expected);
        // If model loops payment methods
        $omiseResult->shouldReceive('getPaymentMethods')
                    ->andReturn($expected);
        // If model filters names
        $omiseResult->shouldReceive('filterPaymentMethodName')
                    ->andReturnUsing(function ($value) {
                        return $value;
                    });
        // If model checks array-access
        $omiseResult->shouldAllowMockingMethod('offsetGet');
        $omiseResult->shouldReceive('offsetGet')
                    ->andReturn($expected);
        // Mock static call OmiseCapability::retrieve()
        $this->omiseCapabilityMock
            ->shouldReceive('retrieve')
            ->andReturn($omiseResult);
        $capability = new Capability($this->configMock);
        $result = $capability->getInstallmentBackends();
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Omise\Payment\Model\Api\Capability
     */
    public function testGetBackendsByType()
    {
        $type = 'installment'; 
        $expected = ['installment'];
        // Mock OmiseCapability result
        $omiseResult = m::mock();
        // Mock method with expected argument
        $omiseResult->shouldReceive('getBackendsByType')
                    ->with($type)  
                    ->andReturn($expected);
        $omiseResult->shouldReceive('getPaymentMethods')->andReturn($expected);
        $omiseResult->shouldReceive('filterPaymentMethodName')
                    ->andReturnUsing(function ($value) { return $value; });
        $omiseResult->shouldAllowMockingMethod('offsetGet');
        $omiseResult->shouldReceive('offsetGet')->andReturn($expected);
        // Mock static retrieve
        $this->omiseCapabilityMock->shouldReceive('retrieve')->andReturn($omiseResult);
        $capability = new Capability($this->configMock);
        // Pass the argument
        $result = $capability->getBackendsByType($type);
        $this->assertEquals($expected, $result);
    }



}
