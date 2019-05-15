define(
    [
        'jquery',
        'ko',
        'mage/storage',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (
        $,
        ko,
        storage,
        Component,
        fullScreenLoader,
        quote,
        urlBuilder,
        errorProcessor
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Omise_Payment/payment/offsite-installment-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            /**
             * Get payment method code
             *
             * @return {string|null}
             */
            getCode: function () {
                return 'omise_offsite_installment';
            },

            /**
             * Initiate observable fields
             *
             * @return this
             */
            initObservable: function () {
                this._super()
                    .observe([
                        'omiseOffsite',
                        'installmentTermsFC',
                        'installmentTermsKTC',
                        'installmentTermsKBank',
                        'installmentTermsBBL',
                        'installmentTermsBAY',
                    ]);

                return this;
            },

            /**
             * Get Installment minimum
             * this function respects info from: https://www.omise.co/installment-payment
             *
             * NOTE: in the future this function should return data from capabilities object.
             *
             * @return {integer}
             */
            getInstallmentMinimum(id) {
                switch (id) {
                    case 'kbank':        return 500;
                    case 'bbl':          return 500;
                    case 'bay':          return 300;
                    case 'first_choice': return 300;
                    case 'ktc':          return 300;
                    default:             return NaN;
                }
            },

            /**
             * Get Installment yearly interest rate
             *
             * NOTE: in the future this function should return data from capabilities object.
             *
             * @return {float}
             */
            getinstallmentInterestRate(id) {
                switch (id) {
                    case 'kbank':        return 0.08;
                    case 'bbl':          return 0.08;
                    case 'bay':          return 0.08;
                    case 'first_choice': return 0.3;
                    case 'ktc':          return 0.08;
                    default:             return NaN;
                }
            },

            /**
             * Get zero interests setting
             *
             * @return {string}
             */
            isZeroInterests() {
                return window.checkoutConfig.is_zero_interest;
            },

            /**
             * Calculates single installment amount
             *
             * @return {integer}
             */
            calculateInstallmentAmount(id, terms) {
                const total = checkoutConfig.quoteData.grand_total;

                if (this.isZeroInterests()) {
                    return (total / terms).toFixed(0)
                }

                // get yearly interests setting
                const rate = this.getinstallmentInterestRate(id);

                // NOTE: total amount should be increased by interests
                // that customer will pay
                // increase ratio is calculated using formula:
                // ratio = 1 + ([yearly interests rate] / [12 months in year] * [number of monthly payments])
                const incTotal = total * (1 + rate / 12 * terms);
                return ( incTotal / terms ).toFixed(0);
            },

            /**
             * Checks if minimum amount is respected
             * 
             * @return {boolean}
             */
            isMinimumAmount(id, terms) {
                const min = this.getInstallmentMinimum(id);
                const total = checkoutConfig.quoteData.grand_total;

                return total / terms >= min;
            },

            /**
             * Get installment terms
             * 
             * @return {string|null}
             */
            getTerms() {
                return this.installmentTermsBBL() || this.installmentTermsKBank() || this.installmentTermsFC() || this.installmentTermsKTC() || this.installmentTermsBAY();
            },

            /**
             * Reset selected terms
             */
            resetTerms() {
                this.installmentTermsBBL(null);
                this.installmentTermsKBank(null);
                this.installmentTermsFC(null);
                this.installmentTermsKTC(null);
                this.installmentTermsBAY(null);
            },

            /**
             * Get a checkout form data
             *
             * @return {Object}
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'offsite': this.omiseOffsite(),
                        'terms': this.getTerms()
                    }
                };
            },

            getFormattedInstallmentTerms: function () {
                var termsStr = getTerms();
                console.log(termsStr);
                return termsStr.substring(0, termsStr.indexOf('months') - 1);
            },

            /**
             * Is method available to display
             *
             * @return {boolean}
             */
            isActive: function () {
                return this.getOrderCurrency().toLowerCase() === 'thb' && this.getStoreCurrency().toLowerCase() === 'thb';
            },

            /**
             * Returns Installment Terms
             *
             * @return {array}
             */
            getInstallmentTerms(id) {
                const installmentBackends = checkoutConfig.installment_backends;
                const templateLabel = $.mage.__('%terms months (%amount THB / month)');

                for (const key in installmentBackends) {
                    if (installmentBackends[key]._id === 'installment_' + id) {
                        const terms = installmentBackends[key].allowed_installment_terms;

                        var dispTerms = [];
                        for (let i = 0; i < terms.length; i++) {
                            if (this.isMinimumAmount(id, terms[i])) {
                                const amount = this.calculateInstallmentAmount(id, terms[i]);
                                dispTerms.push({
                                    label: templateLabel.replace('%terms', terms[i]).replace('%amount', amount),
                                    key: terms[i]
                                });
                            }
                        }

                        return ko.observableArray(
                            dispTerms
                        );
                    }
                }
            },

            /**
             * Get order currency
             *
             * @return {string}
             */
            getOrderCurrency: function () {
                return window.checkoutConfig.quoteData.quote_currency_code;
            },

            /**
             * Get store currency 
             *
             * @return {string}
             */
            getStoreCurrency: function () {
                return window.checkoutConfig.quoteData.store_currency_code;
            },

            /**
             * Check if order value meets minimum requirement
             *
             * @return {boolean}
             */
            orderValueTooLow: function () {
                return window.checkoutConfig.totalsData.base_grand_total < 3000;
            },

            /**
             * Hook the placeOrder function.
             * Original source: placeOrder(data, event); @ module-checkout/view/frontend/web/js/view/payment/default.js
             *
             * @return {boolean}
             */
            placeOrder: function (data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }

                self.getPlaceOrderDeferredObject()
                    .fail(
                        function (response) {
                            errorProcessor.process(response, self.messageContainer);
                            fullScreenLoader.stopLoader();
                            self.isPlaceOrderActionAllowed(true);
                        }
                    ).done(
                        function (response) {
                            var self = this;

                            var serviceUrl = urlBuilder.createUrl(
                                '/orders/:order_id/omise-offsite',
                                {
                                    order_id: response
                                }
                            );

                            storage.get(serviceUrl, false)
                                .fail(
                                    function (response) {
                                        errorProcessor.process(response, self.messageContainer);
                                        fullScreenLoader.stopLoader();
                                        self.isPlaceOrderActionAllowed(true);
                                    }
                                )
                                .done(
                                    function (response) {
                                        if (response) {
                                            $.mage.redirect(response.authorize_uri);
                                        } else {
                                            errorProcessor.process(response, self.messageContainer);
                                            fullScreenLoader.stopLoader();
                                            self.isPlaceOrderActionAllowed(true);
                                        }
                                    }
                                );
                        }
                    );

                return true;
            }
        });
    }
);
