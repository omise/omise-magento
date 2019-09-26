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
            'offline-tesco'
        ];

        alert('hello');

        METHOD_RENDERERS.forEach(rendererName => {
            rendererList.push({
                type: 'omise_' + rendererName.replace(/-/g, '_'),
                component: 'Omise_Payment/js/view/payment/method-renderer/omise-' + rendererName + '-method'
            });  
        });

        return Component.extend({});
    }
);
