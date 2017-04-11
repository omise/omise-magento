<?php
namespace Omise\Payment\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;
use Omise\Payment\Model\Config\Cc as Config;

class CreditCardStrategyCommand implements CommandInterface
{
    /**
     * @var string
     */
    const ACTION_AUTHORIZE                     = \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE;
    const ACTION_AUTHORIZE_THREEDSECURE        = self::ACTION_AUTHORIZE . '_3ds';
    const ACTION_AUTHORIZE_CAPTURE             = \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE;
    const ACTION_AUTHORIZE_CAPTURETHREEDSECURE = self::ACTION_AUTHORIZE_CAPTURE . '_3ds';

    /**
     * @var string
     */
    const COMMAND_AUTHORIZE_THREEDSECURE        = 'authorize_3ds';
    const COMMAND_AUTHORIZE_CAPTURETHREEDSECURE = 'capture_3ds';

    /**
     * @var \Magento\Payment\Gateway\Command\CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var \Omise\Payment\Model\Config\Cc
     */
    private $config;

    public function __construct(
        CommandPoolInterface $commandPool,
        Config               $config
    ) {
        $this->commandPool = $commandPool;
        $this->config      = $config;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Model\InfoInterface **/
        $payment = SubjectReader::readPayment($commandSubject)->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $order        = $payment->getOrder();
        $totalDue     = $order->getTotalDue();
        $baseTotalDue = $order->getBaseTotalDue();

        switch ($this->getPaymentAction($commandSubject)) {
            case self::ACTION_AUTHORIZE:
                $payment->authorize(true, $baseTotalDue);
                $payment->setAmountAuthorized($totalDue);
                break;

            case self::ACTION_AUTHORIZE_CAPTURE:
                $payment->setAmountAuthorized($totalDue);
                $payment->setBaseAmountAuthorized($baseTotalDue);
                $payment->capture(null);
                break;

            case self::ACTION_AUTHORIZE_THREEDSECURE:
                $this->commandPool->get(self::COMMAND_AUTHORIZE_THREEDSECURE)->execute($commandSubject);
                break;

            case self::ACTION_AUTHORIZE_CAPTURETHREEDSECURE:
                $this->commandPool->get(self::COMMAND_AUTHORIZE_CAPTURETHREEDSECURE)->execute($commandSubject);
                break;

            default:
                throw new CommandException(__('TODO : Rewrite error message'));
                break;
        }

        if (! $this->config->is3DSecureEnabled()) {
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
        if ($this->config->is3DSecureEnabled()) {
            return $commandSubject['paymentAction'] . '_3ds'; 
        }

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
