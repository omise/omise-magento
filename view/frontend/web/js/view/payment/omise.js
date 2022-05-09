define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        const METHOD_RENDERERS = [
            'cc',
            'offsite-internetbanking',
            'offsite-alipay',
            'offsite-fpx',
            'offsite-installment',
            'offsite-truemoney',
            'offline-tesco',
            'offline-paynow',
            'offline-promptpay',
            'offsite-pointsciti',
            'offsite-alipaycn',
            'offsite-alipayhk',
            'offsite-dana',
            'offsite-gcash',
            'offsite-kakaopay',
            'offsite-touchngo',
            'offsite-mobilebanking',
            'offsite-rabbitlinepay',
            'offsite-ocbcpao',
        ];

        METHOD_RENDERERS.forEach(rendererName => {
            rendererList.push({
                type: 'omise_' + rendererName.replace(/-/g, '_'),
                component: 'Omise_Payment/js/view/payment/method-renderer/omise-' + rendererName + '-method'
            });
        });

        rendererList.push(
            {
                type: 'omise_offline_conveniencestore',
                component: 'Omise_Payment/js/view/payment/method-renderer/omise-offline-conveniencestore-method'
            }
        );

        return Component.extend({});
    }
);
