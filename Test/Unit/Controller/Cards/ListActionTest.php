<?php

namespace Omise\Payment\Test\Unit\Controller\Cards;

use Omise\Payment\Controller\Cards\ListAction;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Title as PageTitle;
use Magento\Framework\App\Request\Http;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ListActionTest extends TestCase
{
    /** @var ListAction */
    private $controller;

    /** @var Context|\PHPUnit\Framework\MockObject\MockObject */
    private $contextMock;

    /** @var Session|\PHPUnit\Framework\MockObject\MockObject */
    private $customerSessionMock;

    /** @var PageFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $pageFactoryMock;

    /** @var Page|\PHPUnit\Framework\MockObject\MockObject */
    private $resultPageMock;

    /** @var PageConfig|\PHPUnit\Framework\MockObject\MockObject */
    private $pageConfigMock;

    /** @var PageTitle|\PHPUnit\Framework\MockObject\MockObject */
    private $pageTitleMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->pageFactoryMock = $this->createMock(PageFactory::class);
        $this->resultPageMock = $this->createMock(Page::class);
        $this->pageConfigMock = $this->createMock(PageConfig::class);
        $this->pageTitleMock = $this->createMock(PageTitle::class);

        // Properly chain mocks for execute()
        $this->pageConfigMock->method('getTitle')->willReturn($this->pageTitleMock);
        $this->resultPageMock->method('getConfig')->willReturn($this->pageConfigMock);
        $this->pageFactoryMock->method('create')->willReturn($this->resultPageMock);

        $this->controller = new ListAction(
            $this->contextMock,
            $this->customerSessionMock,
            $this->pageFactoryMock
        );
    }

    /**
     * @covers \Omise\Payment\Controller\Cards\ListAction::__construct
     */
    public function testConstructorAssignsProperties(): void
    {
        $reflection = new \ReflectionClass($this->controller);

        $sessionProp = $reflection->getProperty('customerSession');
        $sessionProp->setAccessible(true);
        $this->assertSame($this->customerSessionMock, $sessionProp->getValue($this->controller));

        $pageFactoryProp = $reflection->getProperty('pageFactory');
        $pageFactoryProp->setAccessible(true);
        $this->assertSame($this->pageFactoryMock, $pageFactoryProp->getValue($this->controller));
    }

    /**
     * @covers \Omise\Payment\Controller\Cards\ListAction::dispatch
     * @uses \Omise\Payment\Controller\Cards\ListAction::__construct
     */
    public function testDispatchWithoutAuthentication(): void
    {
        $this->customerSessionMock->method('authenticate')->willReturn(false);

        $actionFlag = $this->createMock(\Magento\Framework\App\ActionFlag::class);

        $reflection = new \ReflectionClass($this->controller);
        $flagProp = $reflection->getParentClass()->getProperty('_actionFlag');
        $flagProp->setAccessible(true);
        $flagProp->setValue($this->controller, $actionFlag);

        $actionFlag->expects($this->once())
            ->method('set')
            ->with('', 'no-dispatch', true);

        // Use Http request mock (supports getFullActionName)
        $requestMock = $this->createMock(Http::class);
        $requestMock->method('getFullActionName')->willReturn('omise/cards/list');

        $this->controller->dispatch($requestMock);
    }

    /**
     * @covers \Omise\Payment\Controller\Cards\ListAction::execute
     * @uses \Omise\Payment\Controller\Cards\ListAction::__construct
     */
    public function testExecuteReturnsResultPage(): void
    {
        // Expect set() to be called on PageTitle
        $this->pageTitleMock->expects($this->once())
            ->method('set')
            ->with('Stored Credit/Debit Cards');

        $result = $this->controller->execute();

        $this->assertSame($this->resultPageMock, $result);
    }
}
