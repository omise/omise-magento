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
                template: 'Omise_Payment/payment/offsite-atome-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            code: 'omise_offsite_atome',
            restrictedToCurrencies: ['thb', 'sgd', 'myr'],

            /**
             * Initiate observable fields
             *
             * @return this
             */
            initObservable: function () {
                this._super()
                    .observe([
                        'atomePhoneNumber'
                    ])
                return this
            },

            /**
             * Get a checkout form data
             *
             * @return {Object}
             */
            getData: function () {
                let phoneNumber = this.atomePhoneNumber()
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'atome_phone_number': phoneNumber && phoneNumber !== '' ? phoneNumber : this.getCustomerSavedPhoneNumber()
                    }
                }
            },

            /**
             * Get customer phone number saved in profile
             *
             * @return {string}
             */
            getCustomerSavedPhoneNumber: function () {
                let q = quote && quote.billingAddress()
                return q ? q.telephone : ''
            }
        })
    }
)
