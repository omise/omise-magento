define(
    [
        'ko',
        'Omise_Payment/js/view/payment/omise-offsite-method-renderer',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
    ],
    function (
        ko,
        Base,
        Component,
        quote
    ) {
        'use strict';

        return Component.extend(Base).extend({
            defaults: {
                template: 'Omise_Payment/payment/offsite-grabpay-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            code: 'omise_offsite_grabpay',
            restrictedToCurrencies: ['thb', 'sgd', 'myr'],
            logo: {
                file: "images/grabpay.png",
                width: "60",
                height: "22",
                name: "grabpay"
            },
        });
    }
);
