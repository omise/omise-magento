define(
    [
        'ko',
        'Omise_Payment/js/view/payment/omise-base-method-renderer',
        'Magento_Payment/js/view/payment/cc-form',
        'mage/storage',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Checkout/js/model/quote'
    ],
    function (
        ko,
        Base,
        Component,
        storage,
        $,
        validator,
        fullScreenLoader,
        redirectOnSuccessAction,
        quote
    ) {
        'use strict';

        return Component.extend(Base).extend({
            defaults: {
                template: 'Omise_Payment/payment/omise-cc-form'
            },

            redirectAfterPlaceOrder: true,

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            code: 'omise_cc',

            billingAddressCountries: ["US", "GB", "CA"],

            /**
             * Get a checkout form data
             *
             * @return {Object}
             */
            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'omise_card_token': this.omiseCardToken(),
                        'omise_card': this.omiseCard(),
                        'omise_save_card': this.omiseSaveCard()
                    }
                };
            },

            /**
             * Get Omise public key
             *
             * @return {string}
             */
            getPublicKey: function() {
                return window.checkoutConfig.payment.omise_cc.publicKey;
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
                        'omiseCardToken',
                        'omiseCard',
                        'omiseSaveCard'
                    ]);

                return this;
            },

            /**
             * Is 3-D Secure config enabled
             *
             * @return {boolean}
             */
            isThreeDSecureEnabled: function(response) {
                return !(response.authorize_uri === "") ;
            },

            /**
             * @return {boolean}
             */
            isCustomerLoggedIn: function() {
                return window.checkoutConfig.payment.omise_cc.isCustomerLoggedIn;
            },

            /**
             * @return {boolean}
             */
            hasSavedCards: function() {
                return !!this.getCustomerCards().length;
            },

            /**
             * @return {array}
             */
            getCustomerCards: function() {
                return window.checkoutConfig.payment.omise_cc.cards;
            },

            /**
             * @return {bool}
             */
            chargeWithNewCard: function(element){
                $('#payment_form_omise_cc').css({display: 'block'});
                return true;
            },

            /**
             * @return {bool}
             */
            chargeWithSavedCard: function(){
                $('#payment_form_omise_cc').css({display: 'none'});
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
            generateTokenAndPerformPlaceOrderAction: function() {
                const self = this;
                let failHandler = this.buildFailHandler(self);

                this.startPerformingPlaceOrderAction();

                let card = {
                    number           : this.omiseCardNumber(),
                    name             : this.omiseCardHolderName(),
                    expiration_month : this.omiseCardExpirationMonth(),
                    expiration_year  : this.omiseCardExpirationYear(),
                    security_code    : this.omiseCardSecurityCode()
                };
                let selectedBillingAddress = quote.billingAddress();
                if(self.billingAddressCountries.indexOf(selectedBillingAddress.countryId) > -1) {
                    Object.assign(card, this.getSelectedTokenBillingAddress(selectedBillingAddress));
                }
                Omise.setPublicKey(this.getPublicKey());
                Omise.createToken('card', card, function(statusCode, response) {
                    if (statusCode === 200) {
                        self.omiseCardToken(response.id);
                        self.getPlaceOrderDeferredObject()
                            .fail(failHandler)
                            .done(function(order_id) {
                                let serviceUrl = self.getMagentoReturnUrl(order_id);
                                storage.get(serviceUrl, false)
                                    .fail(failHandler)
                                    .done(function (response) {
                                        if (response) {
                                            if(self.isThreeDSecureEnabled(response))
                                                $.mage.redirect(response.authorize_uri);
                                            else if (self.redirectAfterPlaceOrder) {
                                                redirectOnSuccessAction.execute();
                                            }
                                        } else {
                                            failHandler(response);
                                        }
                                    });
                            });
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
                event && event.preventDefault();

                if (typeof Omise === 'undefined') {
                    alert($.mage.__('Unable to process the payment, loading the external card processing library is failed. Please contact the merchant.'));
                    return false;
                }

                let card = this.omiseCard();
                if (card) {
                    this.processOrderWithCard(card);
                    return true;
                }

                if (! this.validate()) {
                    return false;
                }

                this.generateTokenAndPerformPlaceOrderAction();

                return true;
            },

            /**
             * Hook the validate function.
             * Original source: validate(); @ module-checkout/view/frontend/web/js/view/payment/default.js
             *
             * @return {boolean}
             */
            validate: function () {
                let prefix = '#' + this.getCode(),
                    fields = [
                        'CardNumber',
                        'CardHolderName',
                        'CardExpirationMonth',
                        'CardExpirationYear',
                        'CardSecurityCode'
                    ]
                ;

                $(prefix + 'Form').validation();
                return fields.map(f=>$(prefix+f).valid()).every(valid=>valid);
            },

            processOrderWithCard: function () {
                const self = this;
                let failHandler = this.buildFailHandler(self);

                self.getPlaceOrderDeferredObject()
                    .fail(failHandler)
                    .done(function(order_id) {
                        let serviceUrl = self.getMagentoReturnUrl(order_id);
                        storage.get(serviceUrl, false)
                            .fail(failHandler)
                            .done(function (response) {
                                if (response) {
                                    if(self.isThreeDSecureEnabled(response))
                                        $.mage.redirect(response.authorize_uri);
                                    else if (self.redirectAfterPlaceOrder) {
                                        redirectOnSuccessAction.execute();
                                    }
                                } else {
                                    failHandler(response);
                                }
                            });
                    });
            },

            getSelectedTokenBillingAddress: function(selectedBillingAddress) {
                let address = {
                    state          : selectedBillingAddress.region,
                    postal_code    : selectedBillingAddress.postcode,
                    phone_number   : selectedBillingAddress.telephone,
                    country        : selectedBillingAddress.countryId,
                    city           : selectedBillingAddress.city,
                    street1        : selectedBillingAddress.street[0]
                }

                if(selectedBillingAddress.street[1]) {
                    address.street2 = selectedBillingAddress.street[1]
                }

                return address
            }
        });
    }
);
