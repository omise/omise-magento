<?php

declare(strict_types=1);

namespace Omise\Payment\Test\Unit\Model\Api;

use Exception;
use Omise\Payment\Model\Api\Customer;
use Omise\Payment\Model\Api\Error;
use Omise\Payment\Model\Config\Config;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Omise\Payment\Model\Api\Customer
 */
class CustomerTest extends TestCase
{
    private $config;
    private $customer;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->customer = new Customer($this->config);
    }

    /**
     * @covers \Omise\Payment\Model\Api\Customer::find
     */
    public function testFindReturnsNullWhenCannotInitialize(): void
    {
        $this->config->method('canInitialize')->willReturn(false);

        $this->assertNull($this->customer->find('cust_test'));
    }

    /**
     * @covers \Omise\Payment\Model\Api\Customer::update
     */
    public function testUpdateReturnsSelfOnSuccess(): void
    {
        $dummyObject = new class {
            public function update($params): void
            {}
        };

        $ref = new \ReflectionProperty(Customer::class, 'object');
        $ref->setAccessible(true);
        $ref->setValue($this->customer, $dummyObject);

        $customer = $this->getMockBuilder(Customer::class)
            ->setConstructorArgs([$this->config])
            ->onlyMethods(['refresh'])
            ->getMock();

        $ref->setValue($customer, $dummyObject);

        $customer->expects($this->once())->method('refresh');

        $result = $customer->update(['email' => 'ok@test.com']);

        $this->assertSame($customer, $result);
    }

    /**
     * @covers \Omise\Payment\Model\Api\Customer::cards
     */
    public function testCardsReturnsCards(): void
    {
        $cards = ['card_1', 'card_2'];

        $dummyObject = new class($cards) {
            /** @var array */
            private $cards;
            public function __construct(array $cards)
            {
                $this->cards = $cards;
            }
            public function cards($options = [])
            {
                return $this->cards;
            }
        };

        $ref = new \ReflectionProperty(Customer::class, 'object');
        $ref->setAccessible(true);
        $ref->setValue($this->customer, $dummyObject);

        $this->assertSame($cards, $this->customer->cards());
    }
}
