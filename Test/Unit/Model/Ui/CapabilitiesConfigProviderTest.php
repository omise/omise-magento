<?php

namespace Omise\Payment\Test\Unit\Model\Ui;

use PHPUnit\Framework\TestCase;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Model\Capabilities;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Omise\Payment\Model\Ui\CapabilitiesConfigProvider;

class CapabilitiesConfigProviderTest extends TestCase
{
    private $storeManagerMock;
    private $capabilitiesMock;
    private $helperMock;
    private $paymentListsMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->capabilitiesMock = $this->getMockBuilder(Capabilities::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder(OmiseHelper::class)
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
            $this->helperMock
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
}
