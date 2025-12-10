<?php
namespace Omise\Payment\Test\Unit\Model;

use Omise\Payment\Model\Capability;
use Omise\Payment\Model\Omise;
use Omise\Payment\Model\Api\Capability as OmiseCapabilityAPI;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Helper\OmiseMoney;
use PHPUnit\Framework\TestCase;

class CapabilityTest extends TestCase
{
    private $omise;
    private $capabilityAPI;
    private $helper;
    private $money;
    private $capability;

    protected function setUp(): void
    {
        $this->omise = $this->createMock(Omise::class);
        $this->capabilityAPI = $this->createMock(OmiseCapabilityAPI::class);
        $this->helper = $this->createMock(OmiseHelper::class);
        $this->money = $this->createMock(OmiseMoney::class);

        // Ensure Omise methods are called in constructor
        $this->omise->expects($this->any())->method('defineUserAgent');
        $this->omise->expects($this->any())->method('defineApiVersion');
        $this->omise->expects($this->any())->method('defineApiKeys');

        $this->capability = new Capability(
            $this->omise,
            $this->capabilityAPI,
            $this->helper,
            $this->money
        );
    }

    public function testRetrieveInstallmentBackends()
    {
        $expected = ['backend1', 'backend2'];
        $this->capabilityAPI->method('getInstallmentBackends')->willReturn($expected);

        $this->assertSame($expected, $this->capability->retrieveInstallmentBackends());
    }

    public function testIsZeroInterest()
    {
        $this->capabilityAPI->method('isZeroInterest')->willReturn(true);
        $this->assertTrue($this->capability->isZeroInterest());
    }

    public function testGetBackendsByType()
    {
        $type = 'card';
        $expected = ['card1', 'card2'];
        $this->capabilityAPI->method('getBackendsByType')->with($type)->willReturn($expected);

        $this->assertSame($expected, $this->capability->getBackendsByType($type));
    }

    public function testRetrieveMobileBankingBackends()
    {
        $backends = [
            (object)['name' => 'bank1', '_id' => 'mobile_banking_test'],
            (object)['name' => 'bank2', '_id' => 'credit_card']
        ];
        $this->capabilityAPI->method('getPaymentMethods')->willReturn($backends);

        $result = $this->capability->retrieveMobileBankingBackends();
        $this->assertCount(1, $result);
        $this->assertEquals('mobile_banking_test', current($result)->_id);
    }

    public function testGetPaymentMethods()
    {
        $backends = ['method1', 'method2'];
        $this->capabilityAPI->method('getPaymentMethods')->willReturn($backends);

        $this->assertSame($backends, $this->capability->getPaymentMethods());
    }

    public function testIsBackendEnabled()
    {
        $this->capabilityAPI->method('getBackendsByType')->with('card')->willReturn(['card1']);
        $this->assertTrue($this->capability->isBackendEnabled('card'));

        $this->capabilityAPI->method('getBackendsByType')->with('bank')->willReturn([]);
        $this->assertFalse($this->capability->isBackendEnabled('bank'));
    }

    public function testGetBackendsWithOmiseCode()
    {
        $backend = (object)['name' => 'omise_bank'];
        $this->capabilityAPI->method('getPaymentMethods')->willReturn([$backend]);
        $this->helper->method('getOmiseCodeByOmiseId')->with('omise_bank')->willReturn('OMISE_CODE');

        $result = $this->capability->getBackendsWithOmiseCode();
        $this->assertArrayHasKey('OMISE_CODE', $result);
        $this->assertContains($backend, $result['OMISE_CODE']);
    }

    public function testGetCardBrands()
    {
        $cardBackend = [(object)['card_brands' => ['visa', 'mastercard']]];
        $this->capabilityAPI->method('getBackendsByType')->with('card')->willReturn($cardBackend);

        $this->assertSame(['visa', 'mastercard'], $this->capability->getCardBrands());
    }

    public function testGetTokenizationMethods()
    {
        $methods = ['method1', 'method2'];
        $this->capabilityAPI->method('getTokenizationMethods')->willReturn($methods);

        $this->assertSame($methods, $this->capability->getTokenizationMethods());
    }

    public function testGetInstallmentMinLimit()
    {
        $this->capabilityAPI->method('getInstallmentMinLimit')->willReturn(1000);
        $this->money->method('setAmountAndCurrency')->willReturnSelf();
        $this->money->method('toUnit')->willReturn(10);

        $this->assertSame(10, $this->capability->getInstallmentMinLimit('THB'));
    }

    public function testGetTokenizationMethodsWithOmiseCode()
    {
        $methods = ['method1'];
        $this->capabilityAPI->method('getTokenizationMethods')->willReturn($methods);
        $this->helper->method('getOmiseCodeByOmiseId')->with('method1')->willReturn('OMISE_METHOD');

        $result = $this->capability->getTokenizationMethodsWithOmiseCode();
        $this->assertArrayHasKey('OMISE_METHOD', $result);
        $this->assertContains($methods, $result['OMISE_METHOD']);
    }
}
