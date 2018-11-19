define(
    [
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (
        ko,
        Component,
        fullScreenLoader,
        redirectOnSuccessAction,
        quote,
        errorProcessor
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Omise_Payment/payment/offline-tesco-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            /**
             * Get payment method code
             *
             * @return {string}
             */
            getCode: function() {
                return 'omise_offline_tesco';
            },

            /**
             * Is method available to display
             *
             * @return {boolean}
             */
            isActive: function() {
                return this.getOrderCurrency().toLowerCase() === 'thb' && this.getStoreCurrency().toLowerCase() === 'thb';
            },

            /**
             * Get order currency
             *
             * @return {string}
             */
            getOrderCurrency: function () {
                return window.checkoutConfig.quoteData.quote_currency_code;
            },

            /**
             * Get store currency
             *
             * @return {string}
             */
            getStoreCurrency: function () {
                return window.checkoutConfig.quoteData.store_currency_code;
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
