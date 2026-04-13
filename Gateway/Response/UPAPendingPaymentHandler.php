<?php
namespace Omise\Payment\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;
use Omise\Payment\Helper\OmiseHelper;

class UPAPendingPaymentHandler implements HandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $session = $response['session'];
        $sessionObject = $session->object;
        $sessionId = $session->id;

        if(!empty($sessionId) && $sessionObject == "checkout_session"){
            $stateObject = $handlingSubject['stateObject'];
            $stateObject->setState(Order::STATE_PENDING_PAYMENT);
            $stateObject->setStatus(Order::STATE_PENDING_PAYMENT);
            $stateObject->setIsNotified(false);    
        }
    }
}
