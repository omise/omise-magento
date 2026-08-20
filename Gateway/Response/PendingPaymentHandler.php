<?php
namespace Omise\Payment\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;
use Omise\Payment\Helper\OmiseHelper;

class PendingPaymentHandler implements HandlerInterface
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
        $is3dsecured = $this->helper->is3DSecureEnabled($response['charge']);
        if (!$is3dsecured) {
            return;
        }
        $stateObject = $handlingSubject['stateObject'];
        $stateObject->setState(Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus(Order::STATE_PENDING_PAYMENT);
        $stateObject->setIsNotified(false);
    }
}
