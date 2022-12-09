<?php

namespace Omise\Payment\Service;

use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\CreditmemoService as MagentoCreditmemoService;

class CreditMemoService
{
    /**
     * @param CreditmemoFactory $creditmemoFactory
     * @param MagentoCreditmemoService $creditMemoService
     * @param Invoice $invoice
     */
    public function __construct(
        CreditmemoFactory $creditMemoFactory,
        MagentoCreditmemoService $creditMemoService,
        Invoice $invoice
    ) {
        $this->creditMemoFactory = $creditMemoFactory;
        $this->creditMemoService = $creditMemoService;
        $this->invoice = $invoice;
    }

    /**
     * @param Order $order
     *
     * @return void
     */
    public function create($order)
    {
        $invoices = $order->getInvoiceCollection();

        foreach ($invoices as $invoice) {
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
