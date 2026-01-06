<?php

namespace Omise\Payment\Test\Unit\Model\Ui;

use PHPUnit\Framework\TestCase;
use Omise\Payment\Helper\RequestHelper;
use Omise\Payment\Model\Capability;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Omise\Payment\Model\Config\Rabbitlinepay;
use Omise\Payment\Model\Config\Shopeepay;
use Omise\Payment\Model\Config\Truemoney;
use Omise\Payment\Model\Ui\CapabilityConfigProvider;

class CapabilityConfigProviderTest extends TestCase
{
    private $storeManagerMock;
    private $capabilityMock;
    private $requestHelper;
    private $paymentListsMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->capabilityMock = $this->getMockBuilder(Capability::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestHelper = $this->getMockBuilder(RequestHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentListsMock = $this->getMockBuilder(PaymentMethodListInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @covers Omise\Payment\Model\Ui\CapabilityConfigProvider
     */
    public function testGetTruemoneyBackendByType()
    {
        $provider = new CapabilityConfigProvider(
            $this->capabilityMock,
            $this->paymentListsMock,
            $this->storeManagerMock,
            $this->requestHelper
        );

        $backends = [
            (object)[
                "name" => "truemoney_jumpapp",
                "currencies" => ["thb"],
                "amount" => ["min" => 2000, "max" => 500000000000]
            ],
            (object)[
                "name" => "truemoney_mpm",
                "currencies" => ["thb"],
                "amount" => ["min" => 1000, "max" => 500000000000]
            ]
        ];

        $result = $this->invokeMethod($provider, 'getTruemoneyBackendByType', [$backends]);

        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
        $this->assertEquals('truemoney_jumpapp', $result[0]->name);
        $this->assertEquals(["thb"], $result[0]->currencies);
        $this->assertEquals(2000, $result[0]->amount['min']);
        $this->assertEquals(500000000000, $result[0]->amount['max']);
    }

    /**
     * @covers Omise\Payment\Model\Ui\CapabilityConfigProvider
     * @dataProvider activeBackends
     */
    public function testFilterActiveBackends($code, $backend)
    {
        $expected = [$code => $backend];
        $this->capabilityMock->method('getBackendsWithOmiseCode')->willReturn($expected);
        $this->capabilityMock->method('getTokenizationMethodsWithOmiseCode')->willReturn([]);

        $provider = new CapabilityConfigProvider(
            $this->capabilityMock,
            $this->paymentListsMock,
            $this->storeManagerMock,
            $this->requestHelper
        );

        $paymentList = [];
        $this->invokeMethod($provider, 'filterActiveBackends', [$code, &$paymentList]);
        $this->assertEquals($expected, $paymentList);
    }

    /**
     * @covers Omise\Payment\Model\Ui\CapabilityConfigProvider
     */
    public function testGetShopeeBackendByTypeMobileJumpAppEnabled()
    {
        $provider = new CapabilityConfigProvider(
            $this->capabilityMock,
            $this->paymentListsMock,
            $this->storeManagerMock,
            $this->requestHelper
        );

        $backends = [
            (object)['name' => Shopeepay::JUMPAPP_ID],
            (object)['name' => Shopeepay::ID],
        ];

        $this->requestHelper->method('isMobilePlatform')->willReturn(true);
        $this->capabilityMock->method('isBackendEnabled')->willReturnMap([
            [Shopeepay::JUMPAPP_ID, true],
            [Shopeepay::ID, true],
        ]);

        $result = $this->invokeMethod($provider, 'getShopeeBackendByType', [$backends]);

        $this->assertCount(1, $result);
        $this->assertEquals(Shopeepay::JUMPAPP_ID, $result[0]->name);
    }

    /**
     * @covers Omise\Payment\Model\Ui\CapabilityConfigProvider
     */
    public function testGetShopeeBackendByTypeNonMobileShopeeEnabled()
    {
        $provider = new CapabilityConfigProvider(
            $this->capabilityMock,
            $this->paymentListsMock,
            $this->storeManagerMock,
            $this->requestHelper
        );

        $backends = [
            (object)['name' => Shopeepay::JUMPAPP_ID],
            (object)['name' => Shopeepay::ID],
        ];

        $this->requestHelper->method('isMobilePlatform')->willReturn(false);
        $this->capabilityMock->method('isBackendEnabled')->willReturnMap([
            [Shopeepay::JUMPAPP_ID, true],
            [Shopeepay::ID, true],
        ]);

        $result = $this->invokeMethod($provider, 'getShopeeBackendByType', [$backends]);

        $this->assertCount(1, $result);
        $this->assertEquals(Shopeepay::ID, $result[0]->name);
    }

    /**
     * @covers Omise\Payment\Model\Ui\CapabilityConfigProvider
     */
    public function testGetShopeeBackendByTypeShopeeDisabled()
    {
        $provider = new CapabilityConfigProvider(
            $this->capabilityMock,
            $this->paymentListsMock,
            $this->storeManagerMock,
            $this->requestHelper
        );

        $backends = [
            (object)['name' => Shopeepay::JUMPAPP_ID],
            (object)['name' => Shopeepay::ID],
        ];

        $this->requestHelper->method('isMobilePlatform')->willReturn(false);
        $this->capabilityMock->method('isBackendEnabled')->willReturnMap([
            [Shopeepay::JUMPAPP_ID, true],
            [Shopeepay::ID, false],
        ]);

        $result = $this->invokeMethod($provider, 'getShopeeBackendByType', [$backends]);

        $this->assertCount(1, $result);
        $this->assertEquals(Shopeepay::JUMPAPP_ID, $result[0]->name);
    }

    /**
     * @covers Omise\Payment\Model\Ui\CapabilityConfigProvider
     */
    public function testGetConfig()
    {
        // Mock store
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->method('getId')->willReturn(1);
        $storeMock->method('getCurrentCurrencyCode')->willReturn('thb');
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);

        // Active payment methods
        $ccGooglePayMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ccGooglePayMock->method('getCode')->willReturn(\Omise\Payment\Model\Config\CcGooglePay::CODE);

        $installmentMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $installmentMock->method('getCode')->willReturn(\Omise\Payment\Model\Config\Installment::CODE);

        $activeMethods = [$ccGooglePayMock, $installmentMock];
        $this->paymentListsMock->method('getActiveList')->willReturn($activeMethods);

        // Capability responses
        $this->capabilityMock->method('getInstallmentMinLimit')->with('thb')->willReturn(1000);
        $this->capabilityMock->method('isZeroInterest')->willReturn(true);
        $this->capabilityMock->method('getCardBrands')->willReturn(['visa', 'mastercard']);

        // Backend arrays for filterActiveBackends
        $this->capabilityMock->method('getBackendsWithOmiseCode')->willReturn([
            \Omise\Payment\Model\Config\CcGooglePay::CODE => [
                (object)[
                    'name' => 'cc_googlepay_backend',
                    'currencies' => ['thb'],
                    'amount' => ['min' => 2000, 'max' => 500000000000]
                ]
            ],
            \Omise\Payment\Model\Config\Installment::CODE => [
                (object)[
                    'name' => 'installment_backend',
                    'currencies' => ['thb'],
                    'amount' => ['min' => 2000, 'max' => 500000000000]
                ]
            ],
        ]);
        $this->capabilityMock->method('getTokenizationMethodsWithOmiseCode')->willReturn([]);

        $provider = new CapabilityConfigProvider(
            $this->capabilityMock,
            $this->paymentListsMock,
            $this->storeManagerMock,
            $this->requestHelper
        );

        $result = $provider->getConfig();

        $this->assertArrayHasKey('omise_installment_min_limit', $result);
        $this->assertEquals(1000, $result['omise_installment_min_limit']);

        $this->assertArrayHasKey('omise_payment_list', $result);
        $this->assertArrayHasKey(\Omise\Payment\Model\Config\CcGooglePay::CODE, $result['omise_payment_list']);
        $this->assertArrayHasKey(\Omise\Payment\Model\Config\Installment::CODE, $result['omise_payment_list']);

        $this->assertArrayHasKey('is_zero_interest', $result);
        $this->assertTrue($result['is_zero_interest']);

        $this->assertArrayHasKey('card_brands', $result);
        $this->assertEquals(['visa', 'mastercard'], $result['card_brands']);
    }

    /**
     * Invoke protected/private method via reflection
     */
    private function invokeMethod(&$object, string $methodName, array $parameters)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Data provider for testFilterActiveBackends
     */
    public function activeBackends()
    {
        return [
            [
                'truemoney',
                [
                    (object)[
                        'name' => 'truemoney_jumpapp',
                        'currencies' => ['thb'],
                        'amount' => ['min' => 2000, 'max' => 500000000000]
                    ]
                ]
            ],
            [
                'shopeepay',
                [
                    (object)[
                        'name' => Shopeepay::JUMPAPP_ID,
                        'currencies' => ['thb', 'sgd', 'myr'],
                        'amount' => ['min' => 2000, 'max' => 500000000000]
                    ]
                ]
            ],
            [
                'rabbitlinepay',
                [
                    (object)[
                        'name' => Rabbitlinepay::ID,
                        'currencies' => ['thb'],
                        'amount' => ['min' => 2000, 'max' => 500000000000]
                    ]
                ]
            ]
        ];
    }
}