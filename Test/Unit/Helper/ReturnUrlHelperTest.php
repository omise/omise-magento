<?php

namespace Omise\Payment\Test\Unit\Helper;

use Omise\Payment\Helper\TokenHelper;
use Omise\Payment\Helper\ReturnUrlHelper;
use Magento\Framework\UrlInterface;

class ReturnUrlHelperTest extends \PHPUnit\Framework\TestCase
{
    protected $model;

    /**
     * This function is called before the test runs.
     * Ideal for setting the values to variables or objects.
     * @coversNothing
     */
    public function setUp(): void
    {
        $this->tokenMock = $this->createMock('Omise\Payment\Helper\TokenHelper'); 
        $this->urlMock = $this->createMock('Magento\Framework\UrlInterface');
        $this->model = new ReturnUrlHelper($this->urlMock, $this->tokenMock);
    }

    /**
     * This function is called after the test runs.
     * Ideal for setting the values to variables or objects.
     * @coversNothing
     */
    public function tearDown(): void
    {
    }

    /**
     * * Test the function returns string with 32 characters by default.
     *
     * @covers \Omise\Payment\Helper\ReturnUrlHelper
     * @test
     */
    public function createReturnsUrlAndToken()
    {
        $expectedToken = 'cadec62c0cf4d12ff0a0a2ba77641aa8c9362eb4c9352762fc39a4d42d8658c9';
        $this->tokenMock->method('random')
            ->willReturn($expectedToken);

        $urlSubstring = 'omise/callback/threedsecure';
        $expectedUrl = "http://localhost/{$urlSubstring}?token={$expectedToken}";

        $this->urlMock->method('getUrl')
            ->willReturn($expectedUrl);

        $urlArray = $this->model->create($urlSubstring);

        $this->assertEquals(count($urlArray), 2);
        $this->assertArrayHasKey('token', $urlArray);
        $this->assertArrayHasKey('url', $urlArray);
        $this->assertEquals($urlArray['url'], $expectedUrl);
        $this->assertEquals($urlArray['token'], $expectedToken);
    }
}
