<?php

namespace Omise\Payment\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class CardFormTheme implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'dark',
                'label' => __('Dark'),
            ],
            [
                'value' => 'light',
                'label' => __('Light')
            ]
        ];
    }
}
