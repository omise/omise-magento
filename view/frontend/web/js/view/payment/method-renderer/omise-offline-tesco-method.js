define(
    [
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/error-processor',
        'Omise_Payment/js/view/payment/payment-tools'
    ],
    function (
        ko,
        Component,
        fullScreenLoader,
        redirectOnSuccessAction,
        quote,
        errorProcessor,
        paymentTools,
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
                return paymentTools.getOrderCurrency().toLowerCase() === 'thb' && paymentTools.getStoreCurrency().toLowerCase() === 'thb';
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
