define(
    [
        'ko',
        'Magento_Payment/js/view/payment/cc-form',
        'mage/translate',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Checkout/js/model/quote'
    ],
    function (
        ko,
        Component,
        $t,
        $,
        validator,
        fullScreenLoader,
        redirectOnSuccessAction,
        quote
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Omise_Payment/payment/omise-cc-form'
            },

            redirectAfterPlaceOrder: true,

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            /**
             * Get payment method code
             *
             * @return {string}
             */
            getCode: function() {
                return 'omise';
            },

            /**
             * Get a checkout form data
             *
             * @return {Object}
             */
            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'omise_card_token': this.omiseCardToken()
                    }
                };
            },

            /**
             * Get Omise public key
             *
             * @return {string}
             */
            getPublicKey: function() {
                return window.checkoutConfig.payment.omise.publicKey;
            },

            /**
             * Initiate observable fields
             *
             * @return this
             */
            initObservable: function() {
                this._super()
                    .observe([
                        'omiseCardNumber',
                        'omiseCardHolderName',
                        'omiseCardExpirationMonth',
                        'omiseCardExpirationYear',
                        'omiseCardSecurityCode',
                        'omiseCardToken'
                    ]);

                return this;
            },

            /**
             * Is method available to display
             *
             * @return {boolean}
             */
            isActive: function() {
                return true;
            },

            /**
             * Start performing place order action,
             * by disable a place order button and show full screen loader component.
             */
            startPerformingPlaceOrderAction: function() {
                this.isPlaceOrderActionAllowed(false);
                fullScreenLoader.startLoader();
            },

            /**
             * Stop performing place order action,
             * by disable a place order button and show full screen loader component.
             */
            stopPerformingPlaceOrderAction: function() {
                fullScreenLoader.stopLoader();
                this.isPlaceOrderActionAllowed(true);
            },

            /**
             * Generate Omise token before proceed the placeOrder process.
             *
             * @return {void}
             */
            generateTokenAndPerformPlaceOrderAction: function(data) {
                var self = this;

                this.startPerformingPlaceOrderAction();

                var card = {
                    number           : this.omiseCardNumber(),
                    name             : this.omiseCardHolderName(),
                    expiration_month : this.omiseCardExpirationMonth(),
                    expiration_year  : this.omiseCardExpirationYear(),
                    security_code    : this.omiseCardSecurityCode()
                };

                Omise.setPublicKey(this.getPublicKey());
                Omise.createToken('card', card, function(statusCode, response) {
                    if (statusCode === 200) {
                        self.omiseCardToken(response.id);
                        self.getPlaceOrderDeferredObject()
                            .fail(
                                function() {
                                    self.stopPerformingPlaceOrderAction();
                                }
                            ).done(
                                function() {
                                    self.afterPlaceOrder();

                                    if (self.redirectAfterPlaceOrder) {
                                        redirectOnSuccessAction.execute();
                                    }
                                }
                            );
                    } else {
                        alert(response.message);
                        self.stopPerformingPlaceOrderAction();
                    }
                });
            },

            /**
             * Hook the placeOrder function.
             * Original source: placeOrder(data, event); @ module-checkout/view/frontend/web/js/view/payment/default.js
             *
             * @return {boolean}
             */
            placeOrder: function(data, event) {
                if (event) {
                    event.preventDefault();
                }

                if (typeof Omise === 'undefined') {
                    alert($t('Unable to process the payment, loading the external card processing library is failed. Please contact the merchant.'));
                    return false;
                }

                if (! this.validate()) {
                    return false;
                }

                this.generateTokenAndPerformPlaceOrderAction(data);

                return true;
            },

            /**
             * Hook the validate function.
             * Original source: validate(); @ module-checkout/view/frontend/web/js/view/payment/default.js
             *
             * @return {boolean}
             */
            validate: function () {
                $('#' + this.getCode() + 'Form').validation();
                
                var isCardNumberValid          = $('#' + this.getCode() + 'CardNumber').valid();
                var isCardHolderNameValid      = $('#' + this.getCode() + 'CardHolderName').valid();
                var isCardExpirationMonthValid = $('#' + this.getCode() + 'CardExpirationMonth').valid();
                var isCardExpirationYearValid  = $('#' + this.getCode() + 'CardExpirationYear').valid();
                var isCardSecurityCodeValid    = $('#' + this.getCode() + 'CardSecurityCode').valid();

                if (isCardNumberValid
                    && isCardHolderNameValid
                    && isCardExpirationMonthValid
                    && isCardExpirationYearValid
                    && isCardSecurityCodeValid) {
                    return true;
                }

                return false;
            },
        });
    }
);
