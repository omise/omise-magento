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
                'label' => __('Dark Theme'),
            ],
            [
                'value' => 'light',
                'label' => __('Light Theme')
            ]
        ];
    }
}
