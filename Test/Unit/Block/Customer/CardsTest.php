<?php

namespace Omise\Payment\Test\Unit\Block\Customer;

use Omise\Payment\Block\Customer\Cards;
use Omise\Payment\Model\Customer as OmiseCustomer;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Omise\Payment\Block\Customer\Cards
 */
class CardsTest extends TestCase
{
    private $contextMock;
    private $customerMock;
    private $cardsBlock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->customerMock = $this->createMock(OmiseCustomer::class);

        $this->cardsBlock = new Cards(
            $this->contextMock,
            $this->customerMock,
            []
        );
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf(Cards::class, $this->cardsBlock);
    }

    /**
     * @covers ::getCards
     * @covers ::__construct
     */
    public function testGetCardsReturnsNullWhenMagentoCustomerIdIsMissing()
    {
        $this->customerMock->method('getMagentoCustomerId')->willReturn(null);
        $this->customerMock->method('getId')->willReturn(123);

        $this->assertNull($this->cardsBlock->getCards());
    }

    /**
     * @covers ::getCards
     * @covers ::__construct
     */
    public function testGetCardsReturnsNullWhenCustomerIdIsMissing()
    {
        $this->customerMock->method('getMagentoCustomerId')->willReturn(456);
        $this->customerMock->method('getId')->willReturn(null);

        $this->assertNull($this->cardsBlock->getCards());
    }

    /**
     * @covers ::getCards
     * @covers ::__construct
     */
    public function testGetCardsReturnsCardsArray()
    {
        $cardsData = [
            ['id' => 'card_1', 'brand' => 'Visa'],
            ['id' => 'card_2', 'brand' => 'MasterCard'],
        ];

        $this->customerMock->method('getMagentoCustomerId')->willReturn(456);
        $this->customerMock->method('getId')->willReturn(789);
        $this->customerMock->method('cards')
            ->with(['order' => 'reverse_chronological'])
            ->willReturn($cardsData);

        $this->assertEquals($cardsData, $this->cardsBlock->getCards());
    }

    /**
     * @covers ::getDeleteLink
     * @covers ::__construct
     */
    public function testGetDeleteLinkReturnsCorrectUrl()
    {
        $card = ['id' => 'card_123'];

        $this->cardsBlock = $this->getMockBuilder(Cards::class)
            ->setConstructorArgs([$this->contextMock, $this->customerMock])
            ->onlyMethods(['getUrl'])
            ->getMock();

        $this->cardsBlock->expects($this->once())
            ->method('getUrl')
            ->with('omise/cards/deleteaction', ['card_id' => 'card_123'])
            ->willReturn('http://example.com/delete/card_123');

        $this->assertEquals('http://example.com/delete/card_123', $this->cardsBlock->getDeleteLink($card));
    }
}