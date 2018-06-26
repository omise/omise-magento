define(
    [
        'jquery',
        'ko',
        'mage/storage',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/redirect-on-success',
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
        redirectOnSuccessAction,
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
             * Is method available to display
             *
             * @return {boolean}
             */
            isActive: function () {
                return true;
            },

            /**
             * Get Omise public key
             *
             * @return {string}
             */
            getPublicKey: function () {
                return window.checkoutConfig.payment.omise_cc.publicKey;
            },

            /**
             * Get order total amount
             *
             * @return {string}
             */
            getOrderAmount: function () {
                return window.checkoutConfig.quoteData.grand_total * (this.getOrderCurrency().toLowerCase()==='jpy' ? 1 : 100);
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
             * Initiate observable fields
             *
             * @return this
             */
            initObservable: function () {
                this._super()
                    .observe([
                        'omiseSource'
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
                        'omise_source': this.omiseSource()
                    }
                };
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

                Omise.setPublicKey(this.getPublicKey());
                Omise.createSource('alipay', { amount: this.getOrderAmount(), currency: this.getOrderCurrency(), }, function (statusCode, response) {
                    self.omiseSource(response.id);
                    self.getPlaceOrderDeferredObject()
                        .fail(
                            function (response) {
                                errorProcessor.process(response, self.messageContainer);
                                fullScreenLoader.stopLoader();
                                self.isPlaceOrderActionAllowed(true);
                            }
                        ).done(
                            function (response) {
                                var self = this;
                                var serviceUrl = urlBuilder.createUrl(
                                    '/orders/:order_id/omise-offsite',
                                    {
                                        order_id: response
                                    }
                                );

                                storage.get(serviceUrl, false)
                                    .fail(
                                        function (response) {
                                            errorProcessor.process(response, self.messageContainer);
                                            fullScreenLoader.stopLoader();
                                            self.isPlaceOrderActionAllowed(true);
                                        }
                                    )
                                    .done(
                                        function (response) {
                                            if (response) {
                                                $.mage.redirect(response.authorize_uri);
                                            } else {
                                                errorProcessor.process(response, self.messageContainer);
                                                fullScreenLoader.stopLoader();
                                                self.isPlaceOrderActionAllowed(true);
                                            }
                                        }
                                    );
                            }
                        );
                    return true;
                });
            }
        });
    }
);
