<?php
namespace Omise\Payment\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;
use Omise\Payment\Helper\OmiseHelper;
use Omise\Payment\Model\Config\Cc as Config;
use Omise\Payment\Model\Api\Charge;
use Magento\Sales\Model\Order\Payment\Transaction;

class CreditCardStrategyCommand implements CommandInterface
{
    /**
     * @var string
     */
    const ACTION_AUTHORIZE                     = \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE;
    const ACTION_AUTHORIZE_CAPTURE             = \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE;

    /**
     * @var string
     */
    const COMMAND_AUTHORIZE       = 'authorize';
    const COMMAND_AUTHORIZE_CAPTURE = 'capture';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var Config
     */
    private $config;

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
     * @param Config $config
     * @param OmiseHelper $helper
     * @param Charge $charge
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        Config               $config,
        OmiseHelper          $helper,
        Charge               $charge
    ) {
        $this->commandPool = $commandPool;
        $this->config      = $config;
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
        $totalDue          = $order->getTotalDue();
        $baseTotalDue      = $order->getBaseTotalDue();

        switch ($this->getPaymentAction($commandSubject)) {
            case self::ACTION_AUTHORIZE:
                $message = $this->commandPool->get(self::COMMAND_AUTHORIZE)->execute($commandSubject);
                //$transaction = $payment->addTransaction(Transaction::TYPE_AUTH);
                //$paymentObject = $payment->authorize(true, $baseTotalDue);
                //$payment->setAmountAuthorized($totalDue);
                break;

            case self::ACTION_AUTHORIZE_CAPTURE:
                $message = $this->commandPool->get(self::COMMAND_AUTHORIZE_CAPTURE)->execute($commandSubject);
                //$transaction = $payment->addTransaction(Transaction::TYPE_CAPTURE);
                //$payment->setAmountAuthorized($totalDue);
                //$payment->setBaseAmountAuthorized($baseTotalDue);
                //$payment->capture(null);
                break;

            default:
                throw new CommandException(__('TODO : Rewrite error message'));
                break;
        }
        //$transaction = $payment->addTransaction(Transaction::TYPE_AUTH);
        //$message = $payment->prependMessage($message);
        //$payment->addTransactionCommentsToOrder($transaction, $message);
        $charge = $this->charge->find($payment->getAdditionalInformation('charge_id'));
        $is3dsecured = $this->helper->is3DSecureEnabled($charge);
        if (! $is3dsecured) {
            $payment->setAdditionalInformation('charge_authorize_uri', "");
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
