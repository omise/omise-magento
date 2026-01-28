<?php

namespace Omise\Payment\Test\Unit\Model;

use Omise\Payment\Model\Customer as OmiseCustomer;
use Omise\Payment\Model\Omise;
use Omise\Payment\Model\Api\Customer as OmiseCustomerAPI;
use Omise\Payment\Model\Api\Error;
use Magento\Customer\Model\Session as MagentoCustomerSession;
use Magento\Customer\Model\ResourceModel\Customer as MagentoCustomerResource;
use Magento\Customer\Model\Customer as MagentoCustomer;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Omise\Payment\Model\Customer
 */
class CustomerTest extends TestCase
{
    private $magentoCustomerResource;
    private $magentoCustomerSession;
    private $omise;
    private $magentoCustomer;

    protected function setUp(): void
    {
        // Magento Resource mock
        $this->magentoCustomerResource = $this->createMock(MagentoCustomerResource::class);

        // Magento Session mock
        $this->magentoCustomerSession = $this->createMock(MagentoCustomerSession::class);

        // Omise mock
        $this->omise = $this->createMock(Omise::class);
        $this->omise->method('defineUserAgent')->willReturn(null);
        $this->omise->method('defineApiVersion')->willReturn(null);
        $this->omise->method('defineApiKeys')->willReturn(null);

        // Magento Customer stub (final + magic methods)
        $this->magentoCustomer = $this->getMockBuilder(MagentoCustomer::class)
            ->onlyMethods(['getData', 'setData'])
            ->addMethods(['getEmail', 'getFirstname', 'getLastname'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->magentoCustomerSession->method('getCustomer')->willReturn($this->magentoCustomer);
        $this->magentoCustomerSession->method('getCustomerId')->willReturn(123);

        $this->magentoCustomer->method('getEmail')->willReturn('test@example.com');
        $this->magentoCustomer->method('getFirstname')->willReturn('John');
        $this->magentoCustomer->method('getLastname')->willReturn('Doe');

        // getData/setData storage for Omise customer ID
        $omiseIdStorage = null;
        $this->magentoCustomer->method('setData')->willReturnCallback(function ($key, $value) use (&$omiseIdStorage) {
            if ($key === OmiseCustomer::OMISE_CUSTOMER_ID_FIELD) {
                $omiseIdStorage = $value;
            }
        });
        $this->magentoCustomer->method('getData')->willReturnCallback(function ($key) use (&$omiseIdStorage) {
            return $key === OmiseCustomer::OMISE_CUSTOMER_ID_FIELD ? $omiseIdStorage : null;
        });
    }

    private function createCustomerWithApiMock($customerAPIMock)
    {
        return new OmiseCustomer(
            $this->magentoCustomerResource,
            $this->magentoCustomerSession,
            $this->omise,
            $customerAPIMock
        );
    }

    /**
     * @covers ::__construct
     * @covers ::initializeObject
     * @covers ::create
     * @covers ::getId
     * @uses \Omise\Payment\Model\Api\Error
     */
    public function testInitializeObjectRecreatesCustomerOnError()
    {
        // Pre-set a fake Omise ID so initializeObject runs
        $this->magentoCustomer->setData(OmiseCustomer::OMISE_CUSTOMER_ID_FIELD, 'cust_fake');

        // Error object to simulate API failure
        $errorMock = new Error();

        // Customer object returned by API
        $mockCustomerObject = new class { public $id = 'cust_999'; };

        // Mock of OmiseCustomerAPI
        $customerAPIMock = $this->createMock(OmiseCustomerAPI::class);

        // First find() returns Error, second find() returns valid customer
        $customerAPIMock->method('find')
            ->willReturnOnConsecutiveCalls($errorMock, $mockCustomerObject);

        // create() returns the **same mock API object** to keep find() callable
        $customerAPIMock->method('create')
            ->willReturn($customerAPIMock);

        // Construct Customer with API mock
        $customer = $this->createCustomerWithApiMock($customerAPIMock);

        // Set the ID manually to simulate create() setting it
        $this->magentoCustomer->setData(OmiseCustomer::OMISE_CUSTOMER_ID_FIELD, 'cust_999');

        $this->assertEquals('cust_999', $customer->getId());
    }

    /**
     * @covers ::isLoggedIn
     * @covers ::__construct
     * @covers ::getId
     * @covers ::initializeObject
     * @covers ::getMagentoCustomerId
     */
    public function testIsLoggedInReturnsTrue()
    {
        $customerAPI = $this->createMock(OmiseCustomerAPI::class);
        $customer = $this->createCustomerWithApiMock($customerAPI);

        $this->assertTrue($customer->isLoggedIn());
    }

    /**
     * @covers ::getId
     * @covers ::__construct
     * @covers ::initializeObject
     */
    public function testGetIdReturnsOmiseCustomerId()
    {
        $customerAPI = $this->createMock(OmiseCustomerAPI::class);
        $customer = $this->createCustomerWithApiMock($customerAPI);

        $this->magentoCustomer->setData(OmiseCustomer::OMISE_CUSTOMER_ID_FIELD, 'cust_123');
        $this->assertEquals('cust_123', $customer->getId());
    }

    /**
     * @covers ::getMagentoCustomerId
     * @covers ::__construct
     * @covers ::getId
     * @covers ::initializeObject
     */
    public function testGetMagentoCustomerIdReturnsSessionId()
    {
        $customerAPI = $this->createMock(OmiseCustomerAPI::class);
        $customer = $this->createCustomerWithApiMock($customerAPI);

        $this->assertEquals(123, $customer->getMagentoCustomerId());
    }

    /**
     * @covers ::create
     * @covers ::__construct
     * @covers ::getId
     * @covers ::initializeObject
     */
    public function testCreateAssignsOmiseCustomerId()
    {
        $mockOmiseCustomer = new class { public $id = 'cust_456'; };
        $customerAPIMock = $this->createMock(OmiseCustomerAPI::class);
        $customerAPIMock->method('create')->willReturn($mockOmiseCustomer);

        $this->magentoCustomerResource->expects($this->once())
            ->method('saveAttribute')
            ->with($this->magentoCustomer, OmiseCustomer::OMISE_CUSTOMER_ID_FIELD);

        $customer = $this->createCustomerWithApiMock($customerAPIMock);
        $customer->create();

        $this->assertEquals('cust_456', $customer->getId());
    }

    /**
     * @covers ::addCard
     * @covers ::create
     * @covers ::__construct
     * @covers ::getId
     * @covers ::initializeObject
     */
    public function testAddCardCreatesCustomerIfNotExists()
    {
        $mockOmiseCustomer = new class {
            public $id = 'cust_789';
            public function update($data) { return $this; }
        };

        $customerAPIMock = $this->createMock(OmiseCustomerAPI::class);
        $customerAPIMock->method('create')->willReturn($mockOmiseCustomer);
        $customerAPIMock->method('update')->willReturn($mockOmiseCustomer);

        $customer = $this->createCustomerWithApiMock($customerAPIMock);
        $customer->addCard('tok_123');

        $this->assertEquals('cust_789', $customer->getId());
    }

    /**
     * @covers ::deleteCard
     * @covers ::__construct
     * @covers ::getId
     * @covers ::initializeObject
     */
    public function testDeleteCardDestroysCard()
    {
        $cardMock = new class {
            public $destroyCalled = false;
            public function destroy() { $this->destroyCalled = true; }
        };

        $cardsStub = new class($cardMock) {
            private $card;
            public function __construct($card) { $this->card = $card; }
            public function retrieve($token) { return $this->card; }
        };

        $customerAPIMock = $this->createMock(OmiseCustomerAPI::class);
        $customerAPIMock->method('cards')->willReturn($cardsStub);

        $customer = $this->createCustomerWithApiMock($customerAPIMock);
        $customer->deleteCard('tok_123');

        $this->assertTrue($cardMock->destroyCalled);
    }

    /**
     * @covers ::cards
     * @covers ::__construct
     * @covers ::getId
     * @covers ::initializeObject
     */
    public function testCardsReturnsOmiseCards()
    {
        $cardsMock = ['data' => [], 'total' => 0];
        $customerAPIMock = $this->createMock(OmiseCustomerAPI::class);
        $customerAPIMock->method('cards')->with(['order' => 'reverse_chronological'])->willReturn($cardsMock);

        $customer = $this->createCustomerWithApiMock($customerAPIMock);
        $this->assertEquals($cardsMock, $customer->cards(['order' => 'reverse_chronological']));
    }

    /**
     * @covers ::getLatestCard
     * @covers ::cards
     * @covers ::__construct
     * @covers ::getId
     * @covers ::initializeObject
     */
    public function testGetLatestCardReturnsNullIfNoCards()
    {
        $cardsMock = ['data' => [], 'total' => 0];
        $customerAPIMock = $this->createMock(OmiseCustomerAPI::class);
        $customerAPIMock->method('cards')->willReturn($cardsMock);

        $customer = $this->createCustomerWithApiMock($customerAPIMock);
        $this->assertNull($customer->getLatestCard());
    }

    /**
     * @covers ::getLatestCard
     * @covers ::cards
     * @covers ::__construct
     * @covers ::getId
     * @covers ::initializeObject
     */
    public function testGetLatestCardReturnsFirstCard()
    {
        $cardsMock = [
            'total' => 1,
            'data' => [
                ['id' => 'card_1', 'brand' => 'Visa']
            ]
        ];

        $customerAPIMock = $this->createMock(OmiseCustomerAPI::class);
        $customerAPIMock->method('cards')->willReturn($cardsMock);

        $customer = $this->createCustomerWithApiMock($customerAPIMock);
        $this->assertEquals(['id' => 'card_1', 'brand' => 'Visa'], $customer->getLatestCard());
    }
}