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
                template: 'Omise_Payment/payment/omise-offsite-upa-installment'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            code: 'omise_offsite_installment',
            restrictedToCurrencies: ['sgd', 'thb'],

            getData: function () {
                return {
                    method: this.item.method,
                    additional_data: {
                        wlb: checkoutConfig.omise_wlb_enable
                    }
                };
            }
        });
    }
);
