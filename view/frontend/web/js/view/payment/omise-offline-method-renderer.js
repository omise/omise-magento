define(
    [
        'Omise_Payment/js/view/payment/omise-base-method-renderer',
        'Magento_Checkout/js/action/redirect-on-success',
    ],
    function (
        Base,
        redirectOnSuccessAction,
    ) {
        'use strict';

        return Object.assign({}, Base, {

            /**
             * Hook the placeOrder function.
             * Original source: placeOrder(data, event); @ module-checkout/view/frontend/web/js/view/payment/default.js
             *
             * @return {boolean}
             */
            placeOrder: function (data, event) {
                var failHandler = this.buildFailHandler(this);

                event && event.preventDefault();

                this.getPlaceOrderDeferredObject()
                    .fail(failHandler)
                    .done(function () {
                        redirectOnSuccessAction.execute();
                    });

                return true;
            }

        });

    }
);
