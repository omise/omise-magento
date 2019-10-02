define(
    [
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (
        errorProcessor,
        fullScreenLoader
    ) {
        'use strict';

        return {

            OFFSITE_RETURN_URL: '/orders/:order_id/omise-offsite',

            /**
             * Get payment method code
             *
             * @return {string}
             */
            getCode: function() {
                return this.code;
            },

            /**
             * Is method available to display
             *
             * @return {boolean}
             */
            isActive: function() {
                if (this.restrictedToCurrencies && this.restrictedToCurrencies.length) {
                    let orderCurrency = this.getOrderCurrency();
                    return (this.getStoreCurrency() == orderCurrency) && this.restrictedToCurrencies.includes(orderCurrency);
                } else {
                    return true;
                }
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
             * Checks if sandbox is turned on
             *
             * @return {boolean}
             */
            isSandboxOn: function () {
                return window.checkoutConfig.isOmiseSandboxOn;
            },

            /**
             * Creates a fail handler for given context
             *
             * @return {boolean}
             */
             buildFailHandler(context) {
                return function (response) {
                    errorProcessor.process(response, context.messageContainer);
                    fullScreenLoader.stopLoader();
                    context.isPlaceOrderActionAllowed(true);
                }
             }

        };

    }
);
