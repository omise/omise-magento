define(
    [
        'ko',
        'Omise_Payment/js/view/payment/omise-offline-method-renderer',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (
        ko,
        Base,
        Component,
        fullScreenLoader,
        quote
    ) {
        'use strict';

        return Component.extend(Base).extend({
            defaults: {
                template: 'Omise_Payment/payment/offline-common-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            code: 'omise_offline_paynow',
            restrictedToCurrencies: ['sgd'],
        });
    }
);
