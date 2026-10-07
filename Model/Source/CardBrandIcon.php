<?php

namespace Omise\Payment\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class CardBrandIcon implements ArrayInterface
{
    /**
     * Return array of supported card brand icons
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'visa',
                'label' => __('Visa'),
            ],
            [
                'value' => 'mastercard',
                'label' => __('Mastercard'),
            ],
            [
                'value' => 'amex',
                'label' => __('American Express'),
            ],
            [
                'value' => 'jcb',
                'label' => __('JCB'),
            ],
            [
                'value' => 'diners',
                'label' => __('Diners Club'),
            ],
            [
                'value' => 'discover',
                'label' => __('Discover'),
            ],
        ];
    }
}
