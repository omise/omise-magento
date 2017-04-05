<?php
namespace Omise\Payment\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;

class PendingPaymentHandler implements HandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var bool **/
        $captured = $response['data']['captured'] ? $response['data']['captured'] : $response['data']['paid'];

        if ($response['data']['status'] === 'pending'
            && $response['data']['authorized'] == false
            && $captured == false
            && $response['data']['authorize_uri']
        ) {
            $stateObject = $handlingSubject['stateObject'];
            $stateObject->setState(Order::STATE_PENDING_PAYMENT);
            $stateObject->setStatus(Order::STATE_PENDING_PAYMENT);
            $stateObject->setIsNotified(false);
        }
    }
}
