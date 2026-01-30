<?php
namespace Omise\Payment\Test\Unit\Controller\Cards;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Model\Session;
use Omise\Payment\Controller\Cards\DeleteAction;
use Omise\Payment\Model\Customer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Omise\Payment\Controller\Cards\DeleteAction
 */
class DeleteActionTest extends TestCase
{
    /** @var DeleteAction */
    private $controller;

    /** @var Http */
    private $requestMock;

    /** @var ManagerInterface */
    private $messageManagerMock;

    /** @var Customer */
    private $customerMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(Http::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->customerMock = $this->createMock(Customer::class);

        $context = $this->createMock(Context::class);
        $context->method('getRequest')->willReturn($this->requestMock);
        $context->method('getMessageManager')->willReturn($this->messageManagerMock);

        $customerSession = $this->createMock(Session::class);

        $this->controller = new DeleteAction(
            $context,
            $customerSession,
            $this->customerMock
        );
    }

    public function testExecuteSuccess(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('card_id')
            ->willReturn('card_123');

        $this->customerMock->expects($this->once())
            ->method('deleteCard')
            ->with('card_123');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage');

        $this->controller->execute();
    }

    public function testExecuteWrongToken(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('card_id')
            ->willReturn(null);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage');

        $this->controller->execute();
    }

    public function testExecuteDeletionException(): void
    {
        $this->requestMock->method('getParam')
            ->with('card_id')
            ->willReturn('card_123');

        $this->customerMock->method('deleteCard')
            ->willThrowException(new \Exception('API error'));

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage');

        $this->controller->execute();
    }

    public function testExecuteWrongRequest(): void
    {
        $wrongRequest = $this->createMock(Http::class); // must be Http
        $wrongRequest->method('getFullActionName')->willReturn('omise_cards_delete');

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage');

        $context = $this->createMock(Context::class);
        $context->method('getRequest')->willReturn($wrongRequest);
        $context->method('getMessageManager')->willReturn($this->messageManagerMock);

        $customerSession = $this->createMock(Session::class);

        $controller = new DeleteAction(
            $context,
            $customerSession,
            $this->customerMock
        );

        $controller->execute();
    }

    public function testDispatchNotAuthenticated(): void
    {
        $request = $this->createMock(Http::class);
        $request->method('getFullActionName')->willReturn('omise_cards_delete');

        $customerSession = $this->createMock(Session::class);
        $customerSession->expects($this->once())
            ->method('authenticate')
            ->willReturn(false);

        $actionFlag = $this->createMock(ActionFlag::class);
        $actionFlag->expects($this->once())
            ->method('set')
            ->with('', 'no-dispatch', true);

        $context = $this->createMock(Context::class);
        $context->method('getRequest')->willReturn($request);
        $context->method('getMessageManager')->willReturn($this->messageManagerMock);

        $controller = new DeleteAction(
            $context,
            $customerSession,
            $this->customerMock
        );

        $reflection = new \ReflectionClass($controller);
        $flagProp = $reflection->getParentClass()->getProperty('_actionFlag');
        $flagProp->setAccessible(true);
        $flagProp->setValue($controller, $actionFlag);

        $controller->dispatch($request);
    }

    public function testDispatchAuthenticated(): void
    {
        $request = $this->createMock(Http::class);
        $request->method('getFullActionName')->willReturn('omise_cards_delete');

        $customerSession = $this->createMock(Session::class);
        $customerSession->expects($this->once())
            ->method('authenticate')
            ->willReturn(true);

        $actionFlag = $this->createMock(ActionFlag::class);
        $actionFlag->expects($this->never())
            ->method('set');

        $context = $this->createMock(Context::class);
        $context->method('getRequest')->willReturn($request);
        $context->method('getMessageManager')->willReturn($this->messageManagerMock);

        $controller = new DeleteAction(
            $context,
            $customerSession,
            $this->customerMock
        );

        $reflection = new \ReflectionClass($controller);
        $flagProp = $reflection->getParentClass()->getProperty('_actionFlag');
        $flagProp->setAccessible(true);
        $flagProp->setValue($controller, $actionFlag);

        $controller->dispatch($request);
    }
}
