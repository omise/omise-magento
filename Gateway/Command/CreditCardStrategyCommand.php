<?php
namespace Omise\Payment\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Model\Api\Charge;
use Magento\Sales\Model\Order\Payment\Transaction;

class CreditCardStrategyCommand implements CommandInterface
{
    /**
     * @var string
     */
    const ACTION_AUTHORIZE                     = \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE;

    /**
     * @var string
     */
    const ACTION_AUTHORIZE_CAPTURE             = \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE;

    /**
     * @var string
     */
    const COMMAND_AUTHORIZE       = 'charge_authorize';
    
    /**
     * @var string
     */
    const COMMAND_AUTHORIZE_CAPTURE = 'charge_capture';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var OmiseHelper
     */
    private $helper;

    /**
     * @var Charge
     */
    private $charge;

    /**
     * @param CommandPoolInterface $commandPool
     * @param OmiseHelper $helper
     * @param Charge $charge
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        OmiseHelper          $helper,
        Charge               $charge
    ) {
        $this->commandPool = $commandPool;
        $this->helper      = $helper;
        $this->charge  = $charge;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Model\InfoInterface **/
        $payment = SubjectReader::readPayment($commandSubject)->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $order             = $payment->getOrder();
        $paymentAction     = $this->getPaymentAction($commandSubject);
        switch ($paymentAction) {
            case self::ACTION_AUTHORIZE:
                $this->commandPool->get(self::COMMAND_AUTHORIZE)->execute($commandSubject);
                break;

            case self::ACTION_AUTHORIZE_CAPTURE:
                $this->commandPool->get(self::COMMAND_AUTHORIZE_CAPTURE)->execute($commandSubject);
                break;

            default:
                throw new CommandException(__('Unable to resolve payment_action type.'));
                break;
        }

        $charge = $this->charge->find($payment->getAdditionalInformation('charge_id'));
        $is3dsecured = $this->helper->is3DSecureEnabled($charge);
        if (! $is3dsecured) {
            $payment->setAdditionalInformation('charge_authorize_uri', "");
            $invoice = $order->getInvoiceCollection()->getLastItem();
            if($paymentAction == self::ACTION_AUTHORIZE_CAPTURE) {
                $invoice->setTransactionId($charge->transaction)->pay();
                $payment->addTransactionCommentsToOrder(
                    $payment->addTransaction(Transaction::TYPE_CAPTURE, $invoice),
                    __(
                        'Captured amount of %1 online via Omise Payment Gateway.',
                        $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal())
                    )
                );
            } else {
                $payment->addTransactionCommentsToOrder(
                    $payment->addTransaction(Transaction::TYPE_AUTH),
                    $payment->prependMessage(
                        __(
                            'Authorized amount of %1 via Omise Payment Gateway.',
                            $order->getBaseCurrency()->formatTxt($order->getTotalDue())
                        )
                    )
                );
            }
            $this->updateOrderState(
                $commandSubject,
                ($order->getState() ? $order->getState() : Order::STATE_PROCESSING),
                ($order->getStatus() ? $order->getStatus() : $order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING))
            );
        }
    }

    /**
    * @param  array  $commandSubject
    *
    * @return string
    */
    protected function getPaymentAction(array $commandSubject)
    {
        return $commandSubject['paymentAction'];
    }
    
    /**
     * @param array  $commandSubject
     * @param string $state
     * @param string $status
     */
    protected function updateOrderState(array $commandSubject, $state, $status)
    {
        $stateObject = SubjectReader::readStateObject($commandSubject);
        $stateObject->setState($state);
        $stateObject->setStatus($status);
    }
}
