<?php

namespace Omise\Payment\Test\Unit\Model\Ui;

use PHPUnit\Framework\TestCase;
use Omise\Payment\Helper\RequestHelper;
use Omise\Payment\Model\Capabilities;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Omise\Payment\Model\Config\Rabbitlinepay;
use Omise\Payment\Model\Config\Shopeepay;
use Omise\Payment\Model\Config\Truemoney;
use Omise\Payment\Model\Ui\CapabilitiesConfigProvider;

class CapabilitiesConfigProviderTest extends TestCase
{
    private $storeManagerMock;
    private $capabilitiesMock;
    private $requestHelper;
    private $paymentListsMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->capabilitiesMock = $this->getMockBuilder(Capabilities::class)
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
     * @covers Omise\Payment\Model\Ui\CapabilitiesConfigProvider
     */
    public function testGetTruemoneyBackendByType()
    {
        $provider = new CapabilitiesConfigProvider(
            $this->capabilitiesMock,
            $this->paymentListsMock,
            $this->storeManagerMock,
            $this->requestHelper
        );

        $expected = [
            (object)[
                "type" => "truemoney_jumpapp",
                "currencies" => [ "thb" ],
                "amount" => [
                    "min" => 2000,
                    "max" => 500000000000
                ]
            ]
        ];

        $result = $this->invokeMethod($provider, 'getTruemoneyBackendByType', [$expected]);

        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider activeBackends
     * @covers Omise\Payment\Model\Ui\CapabilitiesConfigProvider
     */
    public function testFilterActiveBackends($code, $backend)
    {
        $expected = [ $code => $backend ];
        $this->capabilitiesMock->method('getBackendsWithOmiseCode')
            ->willReturn($expected);

        $this->capabilitiesMock->method('getTokenizationMethodsWithOmiseCode')
            ->willReturn([]);

        $provider = new CapabilitiesConfigProvider(
            $this->capabilitiesMock,
            $this->paymentListsMock,
            $this->storeManagerMock,
            $this->requestHelper
        );

        $paymentList = [];
        $this->invokeMethod($provider, 'filterActiveBackends', [$code, &$paymentList]);
        $this->assertEquals($expected, $paymentList);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters)
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
                Truemoney::CODE,
                [
                    (object)[
                        'type' => Truemoney::JUMPAPP_ID,
                        'currencies' => [ 'thb' ],
                        'amount' => [
                            'min' => 2000,
                            'max' => 500000000000
                        ]
                    ]
                ]
            ],
            [
                Shopeepay::CODE,
                [
                    (object)[
                        'type' => Shopeepay::JUMPAPP_ID,
                        'currencies' => [ 'thb', 'sgd', 'myr' ],
                        'amount' => [
                            'min' => 2000,
                            'max' => 500000000000
                        ]
                    ]
                ]
            ],
            [
                Rabbitlinepay::CODE,
                [
                    (object)[
                        'type' => Rabbitlinepay::ID,
                        'currencies' => [ 'thb' ],
                        'amount' => [
                            'min' => 2000,
                            'max' => 500000000000
                        ]
                    ]
                ]
            ]
        ];
    }
}
