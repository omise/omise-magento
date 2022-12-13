<?php

namespace Omise\Payment\Observer\WebhookObserver;

use Magento\Framework\Event\Observer;
use Omise\Payment\Model\Api\Event as ApiEvent;
use Omise\Payment\Model\Order;
use Omise\Payment\Model\Config\Config;
use Magento\Sales\Model\Order as MagentoOrder;
use Omise\Payment\Observer\WebhookObserver\WebhookObserver;
use Omise\Payment\Service\CreditMemoService;

class WebhookRefundObserver extends WebhookObserver
{
    /**
     * @param \Omise\Payment\Model\Api\Event $apiEvent;
     * @param \Omise\Payment\Model\Order $order
     * @param \Omise\Payment\Model\Config\Config $config
     * @param \Omise\Payment\Service\CreditMemoService $creditMemoService
     */
    public function __construct(
        ApiEvent $apiEvent,
        Order $order,
        Config $config,
        CreditMemoService $creditMemoService
    ) {
        parent::__construct($apiEvent, $order, $config);
        $this->creditMemoService = $creditMemoService;
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
        // Todo: Bring back the credit memo creation logic if we find way to restock the quantity

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
