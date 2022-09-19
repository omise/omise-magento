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
                template: 'Omise_Payment/payment/offsite-fpx-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            code: 'omise_offsite_fpx',
            restrictedToCurrencies: ['myr'],
            banks: ko.observable(checkoutConfig.omise_payment_list['omise_offsite_fpx'][0].banks),
            selectedFpxBank: ko.observable(),
            bankLabel: function(name, active) {
                let bankLabel = name;

                if(!active){
                    bankLabel = bankLabel + " (offline)";
                }

                return bankLabel;
            },

            /**
             * Get a checkout form data
             *
             * @return {Object}
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'bank': this.selectedFpxBank()
                    }
                };
            },

        });
    }
);
