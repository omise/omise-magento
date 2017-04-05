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
        return [];
    }
}
