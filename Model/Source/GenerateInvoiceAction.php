<?php

namespace Omise\Payment\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class GenerateInvoiceAction implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT,
                'label' => __('Pending Payment (Default)'),
            ],
            [
                'value' => \Magento\Sales\Model\Order::STATE_PROCESSING,
                'label' => __('Processing')
            ]
        ];
    }
}
