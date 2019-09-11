define(
    [
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Omise_Payment/js/view/payment/payment-tools',
    ],
    function (
        ko,
        Component,
        quote,
        paymentTools,
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Omise_Payment/payment/offsite-truemoney-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            /**
             * Get payment method code
             *
             * @return {string}
             */
            getCode: function () {
                return 'omise_offsite_truemoney';
            },

            /**
             * Initiate observable fields
             *
             * @return this
             */
            initObservable: function () {
                this._super()
                    .observe([
                        'trueMoneyPhoneNumber'
                    ]);
                return this;
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
                        'truemoney_phone_number': this.trueMoneyPhoneNumber() && this.trueMoneyPhoneNumber() !== '' ? this.trueMoneyPhoneNumber() : quote.billingAddress().telephone,
                    }
                };
            },

            /**
             * Get customer phone number saved in profile
             *
             * @return {string}
             */
            getCustomerSavedPhoneNumber: function () {
                return quote.billingAddress().telephone;
            },

            /**
             * Is method available to display
             *
             * @return {boolean}
             */
            isActive: function () {
                return paymentTools.getOrderCurrency() === 'thb' && paymentTools.getStoreCurrency() === 'thb';
            },

            /**
             * Checks if sandbox is turned on
             *
             * @return {boolean}
             */
            isSandboxOn: function () {
                return window.checkoutConfig.isOmiseSandboxOn;
            },

            /**
             * Hook the placeOrder function.
             * Original source: placeOrder(data, event); @ module-checkout/view/frontend/web/js/view/payment/default.js
             *
             * @return {boolean}
             */
            placeOrder: function (data, event) {
                return paymentTools.placeOrder(event, this);
            }
        });
    }
);
