define(
    [
        'Omise_Payment/js/view/payment/omise-base-method-renderer'
    ],
    function (
        Base
    ) {
        'use strict';

        return Object.assign({}, Base, {

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
            }

        });

    }
);
