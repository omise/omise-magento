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
                return this.active;
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
