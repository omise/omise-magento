define(
    [
        'jquery',
        'ko',
        'mage/storage',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (
        $,
        ko,
        storage,
        Component,
        fullScreenLoader,
        quote,
        urlBuilder,
        errorProcessor
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Omise_Payment/payment/offsite-alipay-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            /**
             * Get payment method code
             *
             * @return {string}
             */
            getCode: function () {
                return 'omise_offsite_alipay';
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
             * Is method available to display
             *
             * @return {boolean}
             */
            isActive: function () {
                return this.getOrderCurrency().toLowerCase() === 'thb' && this.getStoreCurrency().toLowerCase() === 'thb';;
            },

            /**
             * Handle payment error
             */
            handlePaymentError: function(response = null) {
                if (response) {
                    errorProcessor.process(response, self.messageContainer);
                }
                
                fullScreenLoader.stopLoader();
                self.isPlaceOrderActionAllowed(true);
            },

            /**
             * Hook the placeOrder function.
             * Original source: placeOrder(data, event); @ module-checkout/view/frontend/web/js/view/payment/default.js
             *
             * @return {boolean}
             */
            placeOrder: function (data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }

                self.getPlaceOrderDeferredObject()
                    .fail(self.handlePaymentError)
                    .done(
                        function (response) {
                            var self = this;

                            var serviceUrl = urlBuilder.createUrl(
                                '/orders/:order_id/omise-offsite',
                                {
                                    order_id: response
                                }
                            );

                            storage.get(serviceUrl, false)
                                .fail(self.handlePaymentError)
                                .done(
                                    function (response) {
                                        if (response) {
                                            $.mage.redirect(response.authorize_uri);
                                        } else {
                                            self.handlePaymentError()
                                        }
                                    }
                                );
                        }
                    );

                return true;
            }
        });
    }
);
