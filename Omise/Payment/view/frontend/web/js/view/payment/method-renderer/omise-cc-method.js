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

            getCode: function() {
                return 'omise';
            },

            isActive: function() {
                return true;
            }
        });
    }
);
