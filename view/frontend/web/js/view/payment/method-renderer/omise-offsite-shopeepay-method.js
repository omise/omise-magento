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
                template: 'Omise_Payment/payment/offsite-common-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            code: 'omise_offsite_shopeepay',
            restrictedToCurrencies: ['myr', 'thb', 'sgd'],
            logo: {
                file: "images/shopeepay.png",
                width: "73",
                height: "30",
                name: "shopeepay"
            }
        });
    }
);
