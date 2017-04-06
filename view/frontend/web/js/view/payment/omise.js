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

        rendererList.push(
            {
                type: 'omise_cc',
                component: 'Omise_Payment/js/view/payment/method-renderer/omise-cc-method'
            }
        );

        return Component.extend({});
    }
);
