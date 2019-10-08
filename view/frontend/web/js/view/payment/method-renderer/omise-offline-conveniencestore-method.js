define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/view/customer',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Catalog/js/price-utils',
        'mage/validation',
    ],
    function (
        $,
        ko,
        Component,
        fullScreenLoader,
        redirectOnSuccessAction,
        quote,
        customer,
        errorProcessor,
        priceUtils
    ) {
        'use strict';

        const convStoreMinimumPurchaseAmount = 200;

        return Component.extend({
            defaults: {
                template: 'Omise_Payment/payment/offline-conveniencestore-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            /**
             * Get payment method code
             *
             * @return {string}
             */
            getCode: function () {
                return 'omise_offline_conveniencestore';
            },

            /**
             * Is method available to display
             *
             * @return {boolean}
             */
            isActive: function () {
                return true;
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
                        'conv_store_phone_number': this.convenienceStorePhoneNumber(),
                        'conv_store_email': this.convenienceStoreEmail(),
                        'conv_store_customer_name': this.getConvenienceStoreCustomersName()
                    }
                };
            },

            /**
             * Initiate observable fields
             *
             * @return this
             */
            initObservable: function () {
                this._super()
                    .observe([
                        'convenienceStorePhoneNumber',
                        'convenienceStoreEmail'
                    ]);
                return this;
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
             * Get total amount of an order
             *
             * @return {integer}
             */
            getTotal: function () {
                return + window.checkoutConfig.totalsData.grand_total;
            },

            /**
             * Get customer name saved in profile
             *
             * @return {string}
             */
            getConvenienceStoreCustomersName: function() {
                return customer().customer().fullname;
            },

            /**
             * Format Price
             * 
             * @param {float} amount - Amount to be formatted
             * @return {string}
             */
            getFormattedAmount: function (amount) {
                return priceUtils.formatPrice(amount, quote.getPriceFormat());
            },

            /* Get formatted message about installment value limitation
            *
            * NOTE: this value should be taken directly from capability object when it is fully implemented.
            *
            * @return {string}
            */
            getMinimumOrderText: function () {
                return $.mage.__('Minimum order value is %amount').replace('%amount', this.getFormattedAmount(convStoreMinimumPurchaseAmount));
            },

            /**
             * Check if order value meets minimum requirement
             *
             * @return {boolean}
             */
            orderValueTooLow: function () {
                return this.getTotal() < convStoreMinimumPurchaseAmount;
            },

            /**
             * Hook the validate function.
             * Original source: validate(); @ module-checkout/view/frontend/web/js/view/payment/default.js
             *
             * @return {boolean}
             */
            validate: function () {
                $('#' + this.getCode() + 'Form').validation();

                var isEmailValid       = $('#' + this.getCode() + 'email').valid();
                var isPhoneNumberValid = $('#' + this.getCode() + 'phoneNumber').valid();

                return isEmailValid && isPhoneNumberValid;
            },

            /**
             * Hook the placeOrder function.
             * Original source: placeOrder(data, event); @ module-checkout/view/frontend/web/js/view/payment/default.js
             *
             * @return {boolean}
             */
            placeOrder: function(data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }

                self.getPlaceOrderDeferredObject()
                    .fail(
                        function(response) {
                            errorProcessor.process(response, self.messageContainer);
                            fullScreenLoader.stopLoader();
                            self.isPlaceOrderActionAllowed(true);
                        }
                    ).done(
                        function() {
                            redirectOnSuccessAction.execute();
                        }
                    );

                return true;
            }
        });
    }
);
