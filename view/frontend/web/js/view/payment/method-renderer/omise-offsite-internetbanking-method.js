define(
    [
        'ko',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Checkout/js/model/quote'
    ],
    function (
        ko,
        Component,
        redirectOnSuccessAction,
        quote
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Omise_Payment/payment/offsite-internetbanking-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            /**
             * Get payment method code
             *
             * @return {string}
             */
            getCode: function() {
                return 'omise_offsite_internetbanking';
            },

            /**
             * Get a checkout form data
             *
             * @return {Object}
             */
            getData: function() {
                return {
                    'method': this.item.method
                };
            },

            /**
             * Is method available to display
             *
             * @return {boolean}
             */
            isActive: function() {
                return true;
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
                        function() {
                            console.log('failed');
                        }
                    ).done(
                        function() {
                            self.afterPlaceOrder();

                            redirectOnSuccessAction.execute();
                        }
                    );

                return true;
            }
        });
    }
);
