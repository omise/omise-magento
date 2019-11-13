define(
    [
        'ko',
        'Omise_Payment/js/view/payment/omise-offsite-placeorder',
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
                template: 'Omise_Payment/payment/offsite-alipay-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            code: 'omise_offsite_alipay',
            restrictedToCurrencies: ['thb']

        });
    }
);
