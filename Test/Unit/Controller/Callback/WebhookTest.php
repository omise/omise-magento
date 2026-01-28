<?php

namespace Omise\Payment\Test\Unit\Controller\Callback;

use Omise\Payment\Controller\Callback\Webhook;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Omise\Payment\Model\Omise;
use Omise\Payment\Model\Api\Event as ApiEvent;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Omise\Payment\Model\Config\Config;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class WebhookTest extends TestCase
{
    private Webhook $controller;
    private Http|MockObject $requestMock;
    private Omise|MockObject $omiseMock;
    private ApiEvent|MockObject $apiEventMock;
    private EventManager|MockObject $eventManagerMock;
    private Config|MockObject $configMock;
    private Context|MockObject $contextMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->omiseMock = $this->createMock(Omise::class);
        $this->apiEventMock = $this->createMock(ApiEvent::class);
        $this->eventManagerMock = $this->createMock(EventManager::class);
        $this->configMock = $this->createMock(Config::class);

        // Omise constructor methods
        $this->omiseMock->expects($this->once())->method('defineUserAgent');
        $this->omiseMock->expects($this->once())->method('defineApiVersion');
        $this->omiseMock->expects($this->once())->method('defineApiKeys');

        $this->controller = new Webhook(
            $this->contextMock,
            $this->requestMock,
            $this->omiseMock,
            $this->apiEventMock,
            $this->eventManagerMock,
            $this->configMock
        );

        // Inject _request into parent Action class
        $reflection = new \ReflectionClass($this->controller);
        $parentReflection = $reflection->getParentClass();
        $requestProp = $parentReflection->getProperty('_request');
        $requestProp->setAccessible(true);
        $requestProp->setValue($this->controller, $this->requestMock);
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\Webhook::__construct
     */
    public function testConstructorAssignsProperties(): void
    {
        $reflection = new \ReflectionClass($this->controller);

        $requestProp = $reflection->getProperty('request');
        $requestProp->setAccessible(true);
        $this->assertSame($this->requestMock, $requestProp->getValue($this->controller));

        $apiEventProp = $reflection->getProperty('apiEvent');
        $apiEventProp->setAccessible(true);
        $this->assertSame($this->apiEventMock, $apiEventProp->getValue($this->controller));

        $eventManagerProp = $reflection->getProperty('eventManager');
        $eventManagerProp->setAccessible(true);
        $this->assertSame($this->eventManagerMock, $eventManagerProp->getValue($this->controller));

        $configProp = $reflection->getProperty('config');
        $configProp->setAccessible(true);
        $this->assertSame($this->configMock, $configProp->getValue($this->controller));
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\Webhook::__construct
     * @covers \Omise\Payment\Controller\Callback\Webhook::execute
     */
    public function testExecuteWebhookDisabled(): void
    {
        $this->configMock->method('isWebhookEnabled')->willReturn(false);
        $this->assertNull($this->controller->execute());
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\Webhook::__construct
     * @covers \Omise\Payment\Controller\Callback\Webhook::execute
     */
    public function testExecuteNonPostRequest(): void
    {
        $this->configMock->method('isWebhookEnabled')->willReturn(true);
        $this->requestMock->method('isPost')->willReturn(false);
        $this->assertNull($this->controller->execute());
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\Webhook::__construct
     * @covers \Omise\Payment\Controller\Callback\Webhook::execute
     */
    public function testExecuteInvalidPayload(): void
    {
        $this->configMock->method('isWebhookEnabled')->willReturn(true);
        $this->requestMock->method('isPost')->willReturn(true);
        $this->requestMock->method('getContent')->willReturn(json_encode(['object' => 'not_event']));
        $this->assertNull($this->controller->execute());
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\Webhook::__construct
     * @covers \Omise\Payment\Controller\Callback\Webhook::execute
     */
    public function testExecuteUnsupportedEvent(): void
    {
        $payload = (object)['object' => 'event', 'id' => 'evt_123'];
        $this->configMock->method('isWebhookEnabled')->willReturn(true);
        $this->requestMock->method('isPost')->willReturn(true);
        $this->requestMock->method('getContent')->willReturn(json_encode($payload));

        $this->apiEventMock->method('find')->with('evt_123')->willReturn($this->apiEventMock);
        $this->apiEventMock->key = 'unsupported.event';
        $this->apiEventMock->data = [];

        $this->eventManagerMock->expects($this->never())->method('dispatch');
        $this->controller->execute();
    }

    /**
     * @covers \Omise\Payment\Controller\Callback\Webhook::__construct
     * @covers \Omise\Payment\Controller\Callback\Webhook::execute
     */
    public function testExecuteDispatchesSupportedEvent(): void
    {
        $payload = (object)['object' => 'event', 'id' => 'evt_123'];
        $this->configMock->method('isWebhookEnabled')->willReturn(true);
        $this->requestMock->method('isPost')->willReturn(true);
        $this->requestMock->method('getContent')->willReturn(json_encode($payload));

        $this->apiEventMock->method('find')->with('evt_123')->willReturn($this->apiEventMock);
        $this->apiEventMock->key = 'charge.complete';
        $this->apiEventMock->data = ['foo' => 'bar'];

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('omise_payment_webhook_charge_complete', ['data' => ['foo' => 'bar']]);

        $this->controller->execute();
    }
}
