define(
    [
        'Magento_Payment/js/view/payment/cc-form',
        'mage/translate',
        'jquery',
    ],
    function (Component, $t, $) {
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
             * Place order function
             *
             * @return {boolean}
             */
            placeOrder: function(data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }

                if (typeof Omise === 'undefined') {
                    alert($t('Unable to process the payment, loading the external card processing library is failed. Please contact the merchant.'));
                    return false;
                }

                if (!self.validate()) {
                    return false;
                }

                var card = {
                    number: this.omiseCardNumber(),
                    name: this.omiseCardHolderName(),
                    expiration_month: this.omiseCardExpirationMonth(),
                    expiration_year: this.omiseCardExpirationYear(),
                    security_code: this.omiseCardSecurityCode()
                };

                Omise.setPublicKey(this.getPublicKey());
                Omise.createToken('card', card, function(statusCode, response) {
                    if (statusCode === 200) {
                        self.omiseCardToken(response.id);
                    } else {
                        alert(response.message);
                    }
                });

                return false;
            },

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
