<?php
namespace Omise\Payment\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;
use Omise\Payment\Helper\OmiseHelper;

class UPAPendingPaymentHandler implements HandlerInterface
{
    /**
     * @var OmiseHelper
     */
    private $helper;

    /**
     * @param OmiseHelper $helper
     */
    public function __construct(OmiseHelper $helper)
    {
        $this->helper = $helper;
    }
    
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        if(array_key_exists('session',$response) && !empty($response['session']['id'])){
            $stateObject = $handlingSubject['stateObject'];
            $stateObject->setState(Order::STATE_PENDING_PAYMENT);
            $stateObject->setStatus(Order::STATE_PENDING_PAYMENT);
            $stateObject->setIsNotified(false);    
        }
    }
}
