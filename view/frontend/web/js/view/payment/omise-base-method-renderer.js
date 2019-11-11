define(
    [
        'jquery',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils'
    ],
    function (
        $,
        errorProcessor,
        fullScreenLoader,
        urlBuilder,
        quote,
        priceUtils
    ) {
        'use strict';

        const RETURN_URL = '/orders/:order_id/omise-offsite';

        return {
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
             * Get formatted message about installment value limitation
             *
             * NOTE: this value should be taken directly from capability object when it is fully implemented.
             *
             * @return {string}
             */
            getMinimumOrderText: function (amount) {
                return $.mage.__('Minimum order value is %amount').replace('%amount', this.getFormattedAmount(amount));
            },

            /**
             * Check if order value meets minimum requirement
             *
             * @return {boolean}
             */
            orderValueTooLow: function (value) {
                return this.getTotal() < value;
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
             * Get return URL for Magento (based on order id)
             *
             * @return {string}
             */
            getMagentoReturnUrl: function (order_id) {
                return urlBuilder.createUrl(
                    RETURN_URL,
                    { order_id }
                );
            },

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
