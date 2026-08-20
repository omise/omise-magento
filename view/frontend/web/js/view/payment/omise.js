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
        
        const METHOD_RENDERERS = Object.keys(window.checkoutConfig.omise_payment_list);

        METHOD_RENDERERS.forEach(rendererName => {
            rendererList.push({
                type: rendererName,
                component: 'Omise_Payment/js/view/payment/method-renderer/' + rendererName.replace(/_/g, '-') + '-method'
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
