<?php
declare(strict_types=1);

namespace Omise\Payment\Test\Unit\Model\Data;

use PHPUnit\Framework\TestCase;
use Omise\Payment\Model\Data\Payment;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\AttributeValueFactory;

/**
 * @coversDefaultClass \Omise\Payment\Model\Data\Payment
 */
class PaymentTest extends TestCase
{
    private $extensionFactory;
    private $attributeFactory;

    protected function setUp(): void
    {
        $this->extensionFactory = $this->createMock(ExtensionAttributesFactory::class);
        $this->attributeFactory = $this->createMock(AttributeValueFactory::class);
    }

    private function createPayment(): Payment
    {
        return new Payment(
            $this->extensionFactory,
            $this->attributeFactory
        );
    }

    /**
     * @covers ::setOrderId
     * @covers ::getOrderId
     */
    public function testOrderIdCanBeSetAndRetrieved()
    {
        $payment = $this->createPayment();

        $result = $payment->setOrderId(987);

        $this->assertSame($payment, $result);
        $this->assertSame(987, $payment->getOrderId());
    }

    /**
     * @covers ::setAuthorizeUri
     * @covers ::getAuthorizeUri
     */
    public function testAuthorizeUriCanBeSetAndRetrieved()
    {
        $payment = $this->createPayment();

        $result = $payment->setAuthorizeUri('https://pay.omise.co/authorize/123');

        $this->assertSame($payment, $result);
        $this->assertSame('https://pay.omise.co/authorize/123', $payment->getAuthorizeUri());
    }

    /**
     * @covers ::getAuthorizeUri
     */
    public function testAuthorizeUriReturnsNullWhenNotSet()
    {
        $payment = $this->createPayment();

        $this->assertNull($payment->getAuthorizeUri());
    }

    /**
     * @covers ::getObject
     */
    public function testGetObjectAlwaysReturnsPayment()
    {
        $payment = $this->createPayment();

        $this->assertSame('payment', $payment->getObject());
    }

    /**
     * @covers ::setOrderId
     * @covers ::setAuthorizeUri
     * @covers ::getOrderId
     * @covers ::getAuthorizeUri
     */
    public function testDataIsStoredIndependently()
    {
        $payment = $this->createPayment();

        $payment->setOrderId(42);
        $payment->setAuthorizeUri('abc123');

        $this->assertSame(42, $payment->getOrderId());
        $this->assertSame('abc123', $payment->getAuthorizeUri());
    }
}
