<?php
namespace Omise\Payment\Gateway\Request\Offsite;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order;

/**
 * This class requires 'initialize' command.
 *
 * A 'stateObject' will be assigned to an array
 * only when the command code is `initialize`.
 */
class OrderPendingPaymentStateBuilder implements BuilderInterface
{
    /**
     * @param  array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $stateObject = $buildSubject['stateObject'];
        $stateObject->setState(Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus(Order::STATE_PENDING_PAYMENT);
        $stateObject->setIsNotified(false);

        return [];
    }
}
