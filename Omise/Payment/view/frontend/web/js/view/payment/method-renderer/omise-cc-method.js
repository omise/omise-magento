define(
    [
        'Magento_Payment/js/view/payment/cc-form'
    ],
    function (Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Omise_Payment/payment/omise-cc-form'
            },

            /**
             * Get payment method code
             *
             * @return {string}
             */
            getCode: function() {
                return 'omise';
            },

            /**
             * Is method available to display
             *
             * @return {boolean}
             */
            isActive: function() {
                return true;
            }
        });
    }
);
