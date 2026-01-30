<?php

namespace Omise\Payment\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Omise\Payment\Gateway\Request\CreditCardBuilder;
use Omise\Payment\Observer\CreditCardDataObserver;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Omise\Payment\Gateway\Request\CreditCardBuilder
 */
class CreditCardBuilderTest extends TestCase
{
    /**
     * @covers ::build
     */
    public function testBuildReturnsChargeIdIfExists()
    {
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $payment   = $this->createMock(Payment::class);

        $paymentDO->method('getPayment')->willReturn($payment);

        $payment->method('getAdditionalInformation')
            ->willReturnMap([
                [CreditCardDataObserver::CHARGE_ID, 'charge_123'],
            ]);

        $builder = new CreditCardBuilder();

        $result = $builder->build(['payment' => $paymentDO]);

        $this->assertSame(['charge_id' => 'charge_123'], $result);
    }

    /**
     * @covers ::build
     */
    public function testBuildReturnsCustomerAndCardIfCustomerExists()
    {
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $payment   = $this->createMock(Payment::class);

        $paymentDO->method('getPayment')->willReturn($payment);

        $payment->method('getAdditionalInformation')
            ->willReturnMap([
                [CreditCardDataObserver::CHARGE_ID, null],
                [CreditCardDataObserver::CUSTOMER, 'cust_123'],
                [CreditCardDataObserver::CARD, 'card_123'],
            ]);

        $builder = new CreditCardBuilder();

        $result = $builder->build(['payment' => $paymentDO]);

        $this->assertSame(
            [
                'customer' => 'cust_123',
                'card'     => 'card_123'
            ],
            $result
        );
    }

    /**
     * @covers ::build
     */
    public function testBuildReturnsCardTokenIfNoChargeIdOrCustomer()
    {
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $payment   = $this->createMock(Payment::class);

        $paymentDO->method('getPayment')->willReturn($payment);

        $payment->method('getAdditionalInformation')
            ->willReturnMap([
                [CreditCardDataObserver::CHARGE_ID, null],
                [CreditCardDataObserver::CUSTOMER, null],
                [CreditCardDataObserver::TOKEN, 'tok_123'],
            ]);

        $builder = new CreditCardBuilder();

        $result = $builder->build(['payment' => $paymentDO]);

        $this->assertSame(['card' => 'tok_123'], $result);
    }
}
