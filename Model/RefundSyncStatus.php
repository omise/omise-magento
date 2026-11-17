<?php

namespace Omise\Payment\Model;

use Omise\Payment\Service\CreditMemoService;
use Magento\Sales\Model\Order;

#[\AllowDynamicProperties]
class RefundSyncStatus
{
    public function __construct(CreditMemoService $creditMemoService)
    {
        $this->creditMemoService = $creditMemoService;
    }

    /**
     * @param object $charge
     * @return boolean
     */
    public function shouldRefund($charge)
    {
        return isset($charge['refunds']) &&
            isset($charge['refunds']['data']) &&
            count($charge['refunds']['data']) !== 0;
    }

    /**
     * @param Order $order
     * @param array $charge
     * @return void
     */
    public function refund($order, $charge)
    {
        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));

        $this->creditMemoService->create($order, $charge['refunded_amount'] / 100);

        $order->addStatusHistoryComment(
            __(
                'Opn Payments: Payment refunded.<br/>An amount of %1 %2 has been refunded (manual sync).',
                number_format($charge['refunded_amount']/100, 2, '.', ''),
                $order->getOrderCurrencyCode()
            )
        );

        $order->save();
    }
}
