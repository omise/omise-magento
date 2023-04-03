<?php

namespace Omise\Payment\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class SecureForm implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'yes',
                'label' => __('Yes'),
            ],
            [
                'value' => 'no',
                'label' => __('No')
            ]
        ];
    }
}
