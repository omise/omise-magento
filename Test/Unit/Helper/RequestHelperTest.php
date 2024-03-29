<?php

namespace Omise\Payment\Test\Unit\Helper;

use Omise\Payment\Helper\RequestHelper;
use Omise\Payment\Test\Mock\RequestMockInterface;

class RequestHelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Omise\Payment\Helper\RequestHelper
     */
    private $model;

    /**
     * @var RequestMockInterface
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    private $headerMock;

    /**
     * This function is called before the test runs.
     * Ideal for setting the values to variables or objects.
     * @coversNothing
     */
    public function setUp() : void
    {
        $this->requestMock = $this->createMock(RequestMockInterface::class);
        $this->headerMock = $this->createMock('\Magento\Framework\HTTP\Header');
        $this->model = new RequestHelper($this->requestMock, $this->headerMock);
    }

    /**
     * Test the function getPlatformType() return correct platform as per user agent
     *
     * @dataProvider platformTypeProvider
     * @covers \Omise\Payment\Helper\RequestHelper
     * @test
     */
    public function getPlatformTypeReturnsCorrectPlatform($platform, $expectedValue)
    {
        $headerMock = $this->headerMock;
        $headerMock->method('getHttpUserAgent')
            ->willReturn($platform);

        $result = $this->model->getPlatformType();
        $this->assertEquals($expectedValue, $result);
    }

    public function platformTypeProvider()
    {
        return [
            ['Android', 'ANDROID'],
            ['android', 'ANDROID'],
            ['ipad', 'IOS'],
            ['IPAD', 'IOS'],
            ['iPad', 'IOS'],
            ['iphone', 'IOS'],
            ['IPHONE', 'IOS'],
            ['iPhone', 'IOS'],
            ['ipod', 'IOS'],
            ['IPOD', 'IOS'],
            ['iPod', 'IOS'],
            ['Mozilla', 'WEB'],
        ];
    }

    /**
     * @covers \Omise\Payment\Helper\RequestHelper
     * @test
     */
    public function getClientIpRemoteAddrHeader()
    {
        $requestMock = $this->requestMock;
        $requestMock->method('getServer')
            ->withConsecutive(
                ['HTTP_CLIENT_IP'],
                ['HTTP_X_FORWARDED_FOR'],
                ['HTTP_X_FORWARDED'],
                ['HTTP_FORWARDED_FOR'],
                ['HTTP_FORWARDED'],
                ['REMOTE_ADDR']
            )
            ->willReturnOnConsecutiveCalls(null, null, null, null, null, '192.168.1.6');

        $result = $this->model->getClientIp();
        $this->assertEquals('192.168.1.6', $result);
    }

    /**
     * @covers \Omise\Payment\Helper\RequestHelper
     * @test
     */
    public function getClientIpHttpXForwadedForHeader()
    {
        $requestMock = $this->requestMock;
        $requestMock->method('getServer')
            ->withConsecutive(
                ['HTTP_CLIENT_IP'],
                ['HTTP_X_FORWARDED_FOR']
            )
            ->willReturnOnConsecutiveCalls(null, '192.168.1.5,192.168.1.6');

        $result = $this->model->getClientIp();
        $this->assertEquals('192.168.1.5', $result);
    }

    /**
     * @covers \Omise\Payment\Helper\RequestHelper
     * @test
     */
    public function getClientIpHttpXForwardedHeader()
    {
        $requestMock = $this->requestMock;
        $requestMock->method('getServer')
            ->withConsecutive(
                ['HTTP_CLIENT_IP'],
                ['HTTP_X_FORWARDED_FOR'],
                ['HTTP_X_FORWARDED'],
            )
            ->willReturnOnConsecutiveCalls(null, null, '192.168.1.8');

        $result = $this->model->getClientIp();
        $this->assertEquals('192.168.1.8', $result);
    }
}
