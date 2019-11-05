define(
    [
        'Omise_Payment/js/view/payment/omise-base-method-renderer',
        'mage/storage',
        'jquery'
    ],
    function (
        Base,
        storage,
        $
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
                var
                    self = this,
                    buildFailHandler = this.buildFailHandler,
                    failHandler = buildFailHandler(self)
                ;

                event && event.preventDefault();

                self.getPlaceOrderDeferredObject()
                    .fail(failHandler)
                    .done(function (order_id) {
                        var
                            storageFailHandler = buildFailHandler(this),
                            serviceUrl = self.getMagentoReturnUrl(order_id)
                        ;
                        storage.get(serviceUrl, false)
                            .fail(storageFailHandler)
                            .done(function (response) {
                                if (response) {
                                    $.mage.redirect(response.authorize_uri);
                                } else {
                                    storageFailHandler(response);
                                }
                            });
                    });

                return true;
            }

        });

    }
);
