<?php

namespace Omise\Payment\Test\Unit\Controller\Payment;

use Omise\Payment\Controller\Payment\Complete;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Title as PageTitle;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class CompleteTest extends TestCase
{
    /** @var Complete */
    private $controller;

    /** @var Context|\PHPUnit\Framework\MockObject\MockObject */
    private $contextMock;

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
        $this->pageFactoryMock = $this->createMock(PageFactory::class);
        $this->resultPageMock = $this->createMock(Page::class);
        $this->pageConfigMock = $this->createMock(PageConfig::class);
        $this->pageTitleMock = $this->createMock(PageTitle::class);

        $this->pageConfigMock->method('getTitle')->willReturn($this->pageTitleMock);
        $this->resultPageMock->method('getConfig')->willReturn($this->pageConfigMock);

        $this->controller = new Complete(
            $this->contextMock,
            $this->pageFactoryMock
        );
    }

    /**
     * @covers \Omise\Payment\Controller\Payment\Complete::__construct
     */
    public function testConstructExecutesSuccessfully(): void
    {
        $controller = new Complete(
            $this->contextMock,
            $this->pageFactoryMock
        );

        $this->assertInstanceOf(Complete::class, $controller);
    }

    /**
     * @covers \Omise\Payment\Controller\Payment\Complete::execute
     * @uses \Omise\Payment\Controller\Payment\Complete::__construct
     */
    public function testExecuteCallsPageFactoryAndReturnsPage(): void
    {
        $this->pageTitleMock->expects($this->once())
            ->method('set')
            ->with('Complete Payment');

        $this->pageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPageMock);

        $result = $this->controller->execute();

        $this->assertInstanceOf(Page::class, $result);
    }
}
