<?php
namespace Omise\Payment\Model;

use Magento\Framework\Exception\LocalizedException;
use Omise\Payment\Helper\OmiseHelper as Helper;
use Omise\Payment\Helper\OmiseEmailHelper as EmailHelper;
use Magento\Sales\Model\Order;
use Omise\Payment\Model\Config\Cc as Config;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\CreditmemoService;

class RefundSyncStatus
{
    /**
     * @param Helper $helper
     * @param EmailHelper $emailHelper
     */
    public function __construct(
        CreditmemoFactory $creditMemoFactory,
        CreditmemoService $creditMemoService,
        Invoice $invoice
    ) {
        $this->creditMemoFactory = $creditMemoFactory;
        $this->creditMemoService = $creditMemoService;
        $this->invoice = $invoice;
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
        $refundedAmount = isset($charge['refunded_amount'])
            ? $charge['refunded_amount']
            : $charge['refunded'];

        $createCreditMemo = $charge['funding_amount'] == $refundedAmount &&
            $order->canCreditmemo() &&
            $order->hasInvoices();

        if ($createCreditMemo) {
            $this->createCredtMemo($order);
            $order->setState(Order::STATE_CLOSED);
            $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CLOSED));
        }

        $order->addStatusHistoryComment(
            __(
                'Opn Payments: Payment refunded.<br/>An amount of %1 %2 has been refunded (manual sync).',
                number_format($charge['refunded_amount']/100, 2, '.', ''),
                $order->getOrderCurrencyCode()
            )
        );

        $order->save();
    }

    /**
     * @param Invoice $invoice
     * @param Order $order
     *
     * @return void
     */
    private function createCredtMemo($order)
    {
        $invoices = $order->getInvoiceCollection();

        foreach($invoices as $invoice) {
            $invoice = $this->invoice->loadByIncrementId($invoice->getIncrementId());
            $creditMemo = $this->creditMemoFactory->createByOrder($order);

            // We don't set invoice as we want to do offline refund
            $creditMemo->setCustomerNote(__('Your Order %1 has been refunded.', $order->getIncrementId()));
            $creditMemo->setCustomerNoteNotify(false);
            $creditMemo->addComment(__('Order has been refunded'));
            $this->creditMemoService->refund($creditMemo);
        }
    }
}
