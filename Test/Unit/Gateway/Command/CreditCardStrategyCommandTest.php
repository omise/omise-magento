<?php
declare(strict_types=1);

namespace Omise\Payment\Test\Unit\Gateway\Command;

use Magento\Directory\Model\Currency;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Omise\Payment\Gateway\Command\CreditCardStrategyCommand;
use Omise\Payment\Helper\OmiseEmailHelper;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Model\Api\Charge;
use PHPUnit\Framework\TestCase;


/**
 * @coversDefaultClass \Omise\Payment\Gateway\Command\CreditCardStrategyCommand
 */
class CreditCardStrategyCommandTest extends TestCase
{
    private CommandPoolInterface $commandPool;
    private OmiseHelper $helper;
    private OmiseEmailHelper $emailHelper;
    private Charge $charge;

    protected function setUp(): void
    {
        $this->commandPool = $this->createMock(CommandPoolInterface::class);
        $this->helper      = $this->createMock(OmiseHelper::class);
        $this->emailHelper = $this->createMock(OmiseEmailHelper::class);
        $this->charge      = $this->createMock(Charge::class);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $command = new CreditCardStrategyCommand(
            $this->commandPool,
            $this->helper,
            $this->emailHelper,
            $this->charge
        );

        $this->assertInstanceOf(CreditCardStrategyCommand::class, $command);
    }

    /**
     * @covers ::execute
     * @covers ::getPaymentAction
     * @covers ::__construct
     */
    public function testAuthorizeWith3DSecureSkipsInvoiceAndEmails(): void
    {
        $command = new CreditCardStrategyCommand(
            $this->commandPool,
            $this->helper,
            $this->emailHelper,
            $this->charge
        );

        $currency = $this->createMock(Currency::class);
        $currency->method('formatTxt')->willReturn('$100.00');

        $order = $this->createMock(Order::class);
        $order->method('getBaseCurrency')->willReturn($currency);

        $payment = $this->createMock(Payment::class);
        $payment->method('getOrder')->willReturn($order);
        $payment->method('getAdditionalInformation')->willReturn('ch_3');

        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $paymentDO->method('getPayment')->willReturn($payment);

        $gatewayCommand = $this->createMock(CommandInterface::class);
        $this->commandPool->method('get')->willReturn($gatewayCommand);

        $chargeObj = new \stdClass();
        $chargeObj->id = 'ch_3';
        $this->charge->method('find')->willReturn($chargeObj);

        // 3DS enabled branch
        $this->helper->method('is3DSecureEnabled')->willReturn(true);
        $this->emailHelper->expects($this->never())->method('sendInvoiceAndConfirmationEmails');

        $stateObject = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['setState', 'setStatus'])
            ->getMock();

        $command->execute([
            'payment'       => $paymentDO,
            'paymentAction' => CreditCardStrategyCommand::ACTION_AUTHORIZE,
            'stateObject'   => $stateObject
        ]);
    }

    /**
     * @covers ::execute
     * @covers ::getPaymentAction
     * @covers ::__construct
     */
    public function testInvalidPaymentActionThrowsException(): void
    {
        $command = new CreditCardStrategyCommand(
            $this->commandPool,
            $this->helper,
            $this->emailHelper,
            $this->charge
        );

        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $paymentDO->method('getPayment')->willReturn($this->createMock(Payment::class));

        $stateObject = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['setState', 'setStatus'])
            ->getMock();

        $this->expectException(\Magento\Payment\Gateway\Command\CommandException::class);

        $command->execute([
            'payment'       => $paymentDO,
            'paymentAction' => 'invalid_action',
            'stateObject'   => $stateObject
        ]);
    }
}
