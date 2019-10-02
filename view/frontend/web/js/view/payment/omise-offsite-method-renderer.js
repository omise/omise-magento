define(
    [
        'Omise_Payment/js/view/payment/omise-base-method-renderer',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'jquery'
    ],
    function (
        Base,
        urlBuilder,
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
                    return_url = this.OFFSITE_RETURN_URL,
                    failHandler = buildFailHandler(self)
                ;

                if (event) {
                    event.preventDefault();
                }

                self.getPlaceOrderDeferredObject()
                    .fail(failHandler)
                    .done(function (order_id) {
                        var
                            self = this,
                            storageFailHandler = buildFailHandler(self),
                            serviceUrl = urlBuilder.createUrl(
                                return_url,
                                { order_id }
                            )
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
