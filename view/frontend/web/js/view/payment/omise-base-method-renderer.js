define(
    [
    
    ],
    function (

    ) {
        'use strict';

        return {

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
            }

        };

    }
);
