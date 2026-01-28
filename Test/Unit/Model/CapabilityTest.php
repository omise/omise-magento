<?php

namespace Omise\Payment\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Omise\Payment\Model\Capability;
use Omise\Payment\Model\Omise;
use Omise\Payment\Model\Api\Capability as CapabilityAPI;
use Omise\Payment\Helper\OmiseMoney;

/**
 * @coversDefaultClass \Omise\Payment\Model\Capability
 */
class CapabilityTest extends TestCase
{
    /**
     * @var Omise
     */
    private $omise;

    /**
     * @var CapabilityAPI
     */
    private $capabilityApi;

    /**
     * @var OmiseHelper
     */
    private $helper;

    /**
     * @var OmiseMoney
     */
    private $money;

    /**
     * @var Capability
     */
    private $model;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->omise = $this->createMock(Omise::class);
        $this->capabilityApi = $this->createMock(CapabilityAPI::class);
        $this->helper = $this->createMock(\Omise\Payment\Helper\OmiseHelper::class);
        $this->money = $this->createMock(\Omise\Payment\Helper\OmiseMoney::class);

        $this->omise->expects($this->once())->method('defineUserAgent');
        $this->omise->expects($this->once())->method('defineApiVersion');
        $this->omise->expects($this->once())->method('defineApiKeys');

        $this->model = new Capability(
            $this->omise,
            $this->capabilityApi,
            $this->helper,
            $this->money
        );
    }

    /**
     * @covers ::__construct
     * @covers ::retrieveInstallmentBackends
     */
    public function testRetrieveInstallmentBackends(): void
    {
        $this->capabilityApi->method('getInstallmentBackends')->willReturn(['a']);
        $this->assertSame(['a'], $this->model->retrieveInstallmentBackends());
    }

    /**
     * @covers ::__construct
     * @covers ::isZeroInterest
     */
    public function testIsZeroInterest(): void
    {
        $this->capabilityApi->method('isZeroInterest')->willReturn(true);
        $this->assertTrue($this->model->isZeroInterest());
    }

    /**
     * @covers ::__construct
     * @covers ::getBackendsByType
     */
    public function testGetBackendsByType(): void
    {
        $this->capabilityApi->method('getBackendsByType')->with('card')->willReturn(['visa']);
        $this->assertSame(['visa'], $this->model->getBackendsByType('card'));
    }

    /**
     * @covers ::__construct
     * @covers ::retrieveMobileBankingBackends
     */
    public function testRetrieveMobileBankingBackends(): void
    {
        $this->capabilityApi->method('getPaymentMethods')->willReturn([
            (object)['name' => 'mobile_banking_kbank'],
            (object)['name' => 'card']
        ]);

        $this->assertCount(1, $this->model->retrieveMobileBankingBackends());
    }

    /**
     * @covers ::__construct
     * @covers ::getPaymentMethods
     */
    public function testGetPaymentMethods(): void
    {
        $this->capabilityApi->method('getPaymentMethods')->willReturn(['card']);
        $this->assertSame(['card'], $this->model->getPaymentMethods());
    }

    /**
     * @covers ::__construct
     * @covers ::isBackendEnabled
     * @covers ::getBackendsByType
     */
    public function testIsBackendEnabledTrue(): void
    {
        $this->capabilityApi->method('getBackendsByType')->with('card')->willReturn(['card']);
        $this->assertTrue($this->model->isBackendEnabled('card'));
    }

    /**
     * @covers ::__construct
     * @covers ::isBackendEnabled
     * @covers ::getBackendsByType
     */
    public function testIsBackendEnabledFalse(): void
    {
        $this->capabilityApi->method('getBackendsByType')->with('wallet')->willReturn(null);
        $this->assertFalse($this->model->isBackendEnabled('wallet'));
    }

    /**
     * @covers ::__construct
     * @covers ::getBackendsWithOmiseCode
     */
    public function testGetBackendsWithOmiseCode(): void
    {
        $this->capabilityApi->method('getPaymentMethods')->willReturn([(object)['name' => 'card']]);
        $this->helper->method('getOmiseCodeByOmiseId')->willReturn('card');

        $result = $this->model->getBackendsWithOmiseCode();
        $this->assertArrayHasKey('card', $result);
    }

    /**
     * @covers ::__construct
     * @covers ::getCardBrands
     * @covers ::getBackendsByType
     */
    public function testGetCardBrands(): void
    {
        $this->capabilityApi->method('getBackendsByType')->willReturn([(object)['card_brands' => ['visa']]]);
        $this->assertSame(['visa'], $this->model->getCardBrands());
    }

    /**
     * @covers ::__construct
     * @covers ::getTokenizationMethods
     */
    public function testGetTokenizationMethods(): void
    {
        $this->capabilityApi->method('getTokenizationMethods')->willReturn(['applepay']);
        $this->assertSame(['applepay'], $this->model->getTokenizationMethods());
    }

    /**
     * @covers ::__construct
     * @covers ::getInstallmentMinLimit
     */
    public function testGetInstallmentMinLimit(): void
    {
        $this->capabilityApi->method('getInstallmentMinLimit')->willReturn(1000);
        $this->money->method('setAmountAndCurrency')->willReturnSelf();
        $this->money->method('toUnit')->willReturn(10);

        $this->assertSame(10, $this->model->getInstallmentMinLimit('THB'));
    }

    /**
     * @covers ::__construct
     * @covers ::getTokenizationMethodsWithOmiseCode
     */
    public function testGetTokenizationMethodsWithOmiseCode(): void
    {
        $this->capabilityApi->method('getTokenizationMethods')->willReturn(['googlepay']);
        $this->helper->method('getOmiseCodeByOmiseId')->willReturn('googlepay');

        $this->assertArrayHasKey(
            'googlepay',
            $this->model->getTokenizationMethodsWithOmiseCode()
        );
    }
}