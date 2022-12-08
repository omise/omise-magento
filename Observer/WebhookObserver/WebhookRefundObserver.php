<?php

namespace Omise\Payment\Observer\WebhookObserver;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omise\Payment\Model\Api\Event as ApiEvent;
use Omise\Payment\Model\Order;
use Omise\Payment\Model\Event\Charge\Complete as EventChargeComplete;
use Omise\Payment\Model\Config\Config;
use Omise\Payment\Model\Api\Charge as ApiCharge;
use Magento\Sales\Model\Order as MagentoOrder;
use Omise\Payment\Observer\WebhookObserver\WebhookObserver;
use Omise\Payment\Model\RefundSyncStatus;

class WebhookRefundObserver extends WebhookObserver
{
    /**
     * @param \Omise\Payment\Model\Api\Event $apiEvent;
     * @param \Omise\Payment\Model\Order $order
     * @param \Omise\Payment\Model\Config\Config $config
     * @param \Omise\Payment\Model\RefundSyncStatus $refundSyncStatus
     */
    public function __construct(
        ApiEvent $apiEvent,
        Order $order,
        Config $config,
        RefundSyncStatus $refundSyncStatus
    ) {
        parent::__construct($apiEvent, $order, $config);
        $this->refundSyncStatus = $refundSyncStatus;
    }

    /**
     * @param Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->setUpExecute($observer)) {
            return;
        }

        if (!$this->orderData->isPaymentReview() && $this->orderData->getState() === MagentoOrder::STATE_PROCESSING) {
            $this->closeOrder();
        }
    }

    /**
     * Complete the transaction and set order status as processing
     *
     * @param OmiseHelper $helper
     * @param OmiseEmailHelper $emailHelper
     * @param bool $isCaptured Is capture pending
     */
    private function closeOrder()
    {
        if ($this->charge->isFullyRefunded()) {
            // Update order state and status.
            $this->refundSyncStatus->createCreditMemo($this->orderData);
            $this->orderData->setState(MagentoOrder::STATE_CLOSED);
            $defaultStatus = $this->orderData->getConfig()->getStateDefaultStatus(MagentoOrder::STATE_CLOSED);
            $this->orderData->setStatus($defaultStatus);
        }

        $refundContextText = $this->charge->isFullyRefunded() ? 'fully' : 'partially';

        $this->orderData->addStatusHistoryComment(
            __(
                "Opn Payments: Payment refunded.<br/>An amount %1 %2 has been {$refundContextText} refunded.",
                $this->orderData->getOrderCurrencyCode(),
                number_format($this->charge->getRefundedAmount(), 2, '.', '')
            )
        );

        $this->orderData->save();
    }
}
