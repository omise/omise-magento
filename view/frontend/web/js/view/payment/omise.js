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
            'offsite-installment',
            'offsite-truemoney',
            'offsite-truepoints',
            'offline-tesco',
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
