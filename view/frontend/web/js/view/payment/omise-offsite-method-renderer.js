define(
    [
        'Omise_Payment/js/view/payment/omise-offline-method-renderer',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'jquery'
    ],
    function (
        Base,
        errorProcessor,
        fullScreenLoader,
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
                var self = this;
                var buildFailHandler = this.buildFailHandler;
                var return_url = this.OFFSITE_RETURN_URL;
                var failHandler = buildFailHandler(self);

                if (event) {
                    event.preventDefault();
                }

                self.getPlaceOrderDeferredObject()
                    .fail(failHandler)
                    .done(function (response) {
                        var
                            self = this,
                            storageFailHandler = buildFailHandler(self),
                            serviceUrl = urlBuilder.createUrl(
                                return_url,
                                { order_id: response }
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
