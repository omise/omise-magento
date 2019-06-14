define(
    [
        'jquery',
        'ko',
        'mage/storage',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Catalog/js/price-utils',
    ],
    function (
        $,
        ko,
        storage,
        Component,
        fullScreenLoader,
        quote,
        urlBuilder,
        errorProcessor,
        priceUtils,
    ) {
        'use strict';
        const installmentMinimumPurchaseAmount = 3000;

        return Component.extend({
            defaults: {
                template: 'Omise_Payment/payment/offsite-installment-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            /**
             * Get payment method code
             *
             * @return {string}
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
             * Format Price
             * 
             * @param {float} amount - Amount to be formatted
             * @return {string}
             */
            getFormattedAmount: function (amount) {
                return priceUtils.formatPrice(amount, quote.getPriceFormat());
            },

            /**
             * Get formatted message about installment value limitation
             *
             * NOTE: this value should be taken directly from capability object when it is fully implemented.
             *
             * @return {string}
             */
            getMinimumOrderText: function () {
                return $.mage.__('Minimum order value is %amount').replace('%amount', this.getFormattedAmount(installmentMinimumPurchaseAmount));
            },

            /**
             * Get Installment minimum
             * this function respects info from: https://www.omise.co/installment-payment
             *
             * NOTE: in the future this function should return data from capabilities object.
             *
             * @param {string} id - Bank ID
             * @return {integer}
             */
            getInstallmentMinimum: function (id) {
                switch (id) {
                    case 'kbank': return 500;
                    case 'bbl': return 500;
                    case 'bay': return 300;
                    case 'first_choice': return 300;
                    case 'ktc': return 300;
                }
            },

            /**
             * Get Installment monthly interest rate
             *
             * NOTE: in the future this function should return data from capabilities object.
             * 
             * @param {string} id - Bank id
             * @return {float}
             */
            getInstallmentInterestRate: function (id) {
                switch (id) {
                    case 'kbank': return 0.0065;
                    case 'bbl': return 0.008;
                    case 'bay': return 0.008;
                    case 'first_choice': return 0.013;
                    case 'ktc': return 0.008;
                }
            },

            /**
             * Get zero interest setting
             *
             * @return {boolean}
             */
            isZeroInterest: function () {
                return window.checkoutConfig.is_zero_interest;
            },

            /**
             * Calculates single installment amount
             * 
             * @param {string} id - Bank ID
             * @param {integer} terms - number of monthly installments
             * @return {integer}
             */
            calculateSingleInstallmentAmount: function (id, terms) {
                const total = this.getTotal();

                if (this.isZeroInterest()) {
                    //merchant pays interest
                    return (total / terms).toFixed(2)
                }

                const rate = this.getInstallmentInterestRate(id);
                const interest = rate * terms * total;

                return + (((total + interest) / terms).toFixed(2));
            },

            /**
             * Get total amount of an order
             *
             * @return {integer}
             */
            getTotal: function () {
                return + window.checkoutConfig.totalsData.grand_total;
            },

            /**
             * Get installment terms
             * 
             * @return {string|null}
             */
            getTerms: function () {
                return this.installmentTermsBBL() || this.installmentTermsKBank() || this.installmentTermsFC() || this.installmentTermsKTC() || this.installmentTermsBAY();
            },

            /**
             * Reset selected terms
             */
            resetTerms: function () {
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

            /**
             * Is method available to display
             *
             * @return {boolean}
             */
            isActive: function () {
                return this.getOrderCurrency().toLowerCase() === 'thb' && this.getStoreCurrency().toLowerCase() === 'thb';
            },

            /**
             * Checks if sandbox is turned on
             *
             * @return {boolean}
             */
            isSandboxOn: function () {
                return window.checkoutConfig.isOmiseSandboxOn;
            },

            /**
             * Returns Installment Terms
             * @param {string} id - Bank id
             * @return {array}
             */
            getInstallmentTerms: function (id) {
                const installmentBackends = checkoutConfig.installment_backends;
                const templateLabel = $.mage.__('%terms months (%amount / month)');

                for (const key in installmentBackends) {
                    if (installmentBackends[key]._id !== 'installment_' + id) {
                        continue;
                    }

                    let dispTerms = [];
                    const terms = installmentBackends[key].allowed_installment_terms;
                    const minSingleInstallment = this.getInstallmentMinimum(id);

                    for (let i = 0; i < terms.length; i++) {
                        const amount = this.calculateSingleInstallmentAmount(id, terms[i]);

                        if (amount >= minSingleInstallment) {
                            dispTerms.push({
                                label: templateLabel.replace('%terms', terms[i]).replace('%amount', this.getFormattedAmount(amount)),
                                key: terms[i]
                            });
                        }
                    }

                    return ko.observableArray(
                        dispTerms
                    );
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
                return this.getTotal() < installmentMinimumPurchaseAmount;
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
