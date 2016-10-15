define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Omise_Payment/payment/omise-cc-form'
            },

            getMailingAddress: function() {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
        });
    }
);
