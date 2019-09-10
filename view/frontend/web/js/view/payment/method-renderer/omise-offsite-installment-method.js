define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Omise_Payment/js/view/payment/payment-tools'
    ],
    function (
        $,
        ko,
        Component,
        quote,
        priceUtils,
        paymentTools,
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
                const total = paymentTools.getTotal();

                if (this.isZeroInterest()) {
                    //merchant pays interest
                    return (total / terms).toFixed(2)
                }

                const rate = this.getInstallmentInterestRate(id);
                const interest = rate * terms * total;

                return + (((total + interest) / terms).toFixed(2));
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
                return paymentTools.getOrderCurrency().toLowerCase() === 'thb' && paymentTools.getStoreCurrency().toLowerCase() === 'thb';
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
             * Check if order value meets minimum requirement
             *
             * @return {boolean}
             */
            orderValueTooLow: function () {
                return paymentTools.getTotal() < installmentMinimumPurchaseAmount;
            },

            /**
             * Place Order function.
             *
             * @return {boolean}
             */
            placeOrder: function (data, event) {
                return paymentTools.placeOrder(event, this);
            }
        });
    }
);
