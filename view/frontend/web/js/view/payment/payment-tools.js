define(
    [
        'jquery',
        'ko',
        'mage/storage',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Catalog/js/price-utils',
    ],
    function (
        $,
        ko,
        storage,
        fullScreenLoader,
        quote,
        urlBuilder,
        errorProcessor,
        priceUtils,
    ) {
        'use strict';

        return {
            defaults: {
                return_url: '/orders/:order_id/omise-offsite'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            /**
             * Format Price
             * 
             * @param {float} amount - Amount to be formatted
             * @return {string}
             */
            getFormattedAmount: function (amount) {
                return priceUtils.formatPrice(amount, quote.getPriceFormat());
            },

            /**
             * Get total amount of an order
             *
             * @return {integer}
             */
            getTotal: function () {
                return window.checkoutConfig.totalsData.grand_total;
            },

            /**
             * Get order currency
             *
             * @return {string}
             */
            getOrderCurrency: function () {
                return window.checkoutConfig.quoteData.quote_currency_code.toLowerCase();
            },

            /**
             * Get store currency
             *
             * @return {string}
             */
            getStoreCurrency: function () {
                return window.checkoutConfig.quoteData.store_currency_code.toLowerCase();
            },

            /**
             * Hook the placeOrder function.
             * Original source: placeOrder(data, event); @ module-checkout/view/frontend/web/js/view/payment/default.js
             *
             * @return {boolean}
             */
            placeOrder: function (event, self) {
                if (event) {
                    event.preventDefault();
                }

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
                                this.return_url,
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
            }
        };
    }
);
