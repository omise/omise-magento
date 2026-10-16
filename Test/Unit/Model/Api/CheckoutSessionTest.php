<?php

namespace Omise\Payment\Test\Unit\Model\Api;

use Exception;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Exception\LocalizedException;
use Omise\Payment\Model\Api\CheckoutSession;
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Helper\RequestHelper;
use OmiseApiResource;

class CheckoutSessionTest extends TestCase
{
    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $config;

    /**
     * @var RequestHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestHelper;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->requestHelper = $this->createMock(RequestHelper::class);
    }
    
    /**
     * @covers Omise\Payment\Model\Api\CheckoutSession
     */
    public function testConstruct()
    {
        $model = new CheckoutSession(
            $this->config,
            $this->requestHelper
        );

        $this->assertInstanceOf(
            CheckoutSession::class,
            $model
        );
    }

    /**
     * @covers Omise\Payment\Model\Api\CheckoutSession
     * @uses \Omise\Payment\Model\Api\BaseObject
     */
    public function testCreateSessionSuccess()
    {
        $params = [
            'amount' => 1000,
            'currency' => 'THB'
        ];

        $sessionResponse = [
            'id' => 'sess_test_123',
            'object' => 'checkout_session'
        ];

        $model = new CheckoutSession(
            $this->config,
            $this->requestHelper
        );

        $this->config->expects($this->once())
            ->method('checkoutSessionEndpoint')
            ->willReturn('https://checkout-page.omise.co/');

        $this->config->expects($this->once())
            ->method('getSecretKey')
            ->willReturn('sk_test');

        $this->requestHelper->expects($this->once())
            ->method('sendUpaSessionRequest')
            ->with(
                'https://checkout-page.omise.co/api/sessions',
                OmiseApiResource::REQUEST_POST,
                'sk_test',
                $params,
                true
            )
            ->willReturn($sessionResponse);

        $result = $model->createSession($params);

        $this->assertSame($model, $result);
        $this->assertEquals('sess_test_123', $model->id);
        $this->assertEquals('checkout_session', $model->object);
    }

    /**
     * @covers Omise\Payment\Model\Api\CheckoutSession
     * @uses \Omise\Payment\Model\Api\BaseObject
     */
    public function testGetSessionInfoSuccess()
    {
        $sessionId = 'sess_test_123';

        $sessionResponse = [
            'id' => 'sess_test_123',
            'object' => 'checkout_session'
        ];

        $model = new CheckoutSession(
            $this->config,
            $this->requestHelper
        );

        $this->config->expects($this->once())
            ->method('checkoutSessionEndpoint')
            ->willReturn('https://checkout-page.omise.co/');

        $this->config->expects($this->once())
            ->method('getSecretKey')
            ->willReturn('sk_test');

        $this->requestHelper->expects($this->once())
            ->method('sendUpaSessionRequest')
            ->with(
                'https://checkout-page.omise.co/api/sessions/' . $sessionId,
                OmiseApiResource::REQUEST_GET,
                'sk_test'
            )
            ->willReturn($sessionResponse);

        $result = $model->getSessionInfo($sessionId);

        $this->assertSame($model, $result);
        $this->assertEquals('sess_test_123', $model->id);
        $this->assertEquals('checkout_session', $model->object);
    }

    /**
     * @covers Omise\Payment\Model\Api\CheckoutSession
     */
    public function testCreateSessionThrowsLocalizedException()
    {
        $params = [
            'amount' => 1000
        ];

        $model = new CheckoutSession(
            $this->config,
            $this->requestHelper
        );

        $this->config->expects($this->once())
            ->method('checkoutSessionEndpoint')
            ->willReturn('https://checkout-page.omise.co/');

        $this->requestHelper->expects($this->once())
            ->method('sendUpaSessionRequest')
            ->willThrowException(
                new Exception('API Error')
            );

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Failed to create session : API Error');
        $model->createSession($params);
    }

    /**
     * @covers Omise\Payment\Model\Api\CheckoutSession
     */
    public function testGetSessionInfoThrowsLocalizedException()
    {
        $sessionId = 'sess_test_123';

        $model = new CheckoutSession(
            $this->config,
            $this->requestHelper
        );

        $this->config->expects($this->once())
            ->method('checkoutSessionEndpoint')
            ->willReturn('https://checkout-page.omise.co/');

        $this->requestHelper->expects($this->once())
            ->method('sendUpaSessionRequest')
            ->willThrowException(
                new Exception('API Error')
            );

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Failed to get session info : API Error');
        $model->getSessionInfo($sessionId);
    }
}
