define(
    [
        'jquery',
        'ko',
        'Omise_Payment/js/view/payment/omise-offsite-method-renderer',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils'
    ],
    function (
        $,
        ko,
        Base,
        Component,
        quote,
        priceUtils
    ) {
        'use strict';
        const INSTALLMENT_MIN_PURCHASE_AMOUNT = 2000;
        const CAPTION = 'Choose number of monthly payments';

        return Component.extend(Base).extend({
            defaults: {
                template: 'Omise_Payment/payment/offsite-installment-form'
            },

            code: 'omise_offsite_installment',
            restrictedToCurrencies: ['thb'],
            providers: [
                {
                    id: "installment_ktc",
                    title: 'Krungthai Card',
                    code: 'ktc',
                    logo: 'ktc',
                    obs: 'installmentTermsKTC',
                    active: true
                },
                {
                    id: "installment_first_choice",
                    title: 'First Choice',
                    code: 'first_choice',
                    logo: 'fc',
                    obs: 'installmentTermsFC',
                    active: true
                },
                {
                    id: "installment_kbank",
                    title: 'Kasikorn Bank',
                    code: 'kbank',
                    logo: 'kbank',
                    obs: 'installmentTermsKBank',
                    active: true
                },
                {
                    id: "installment_bbl",
                    title: 'Bangkok Bank',
                    code: 'bbl',
                    logo: 'bbl',
                    obs: 'installmentTermsBBL',
                    active: true
                },
                {
                    id: "installment_bay",
                    title: 'Krungsri',
                    code: 'bay',
                    logo: 'bay',
                    obs: 'installmentTermsBAY',
                    active: true
                },
                {
                    id: "installment_scb",
                    title: 'Siam Commercial Bank',
                    code: 'scb',
                    logo: 'scb',
                    obs: 'installmentTermsSCB',
                    active: true
                },
                {
                    id: "installment_uob",
                    title: 'United Overseas Bank',
                    code: 'uob',
                    logo: 'uob',
                    obs: 'installmentTermsUOB',
                    active: true
                },

            ],

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
                        'installmentTermsSCB',
                        'installmentTermsUOB',
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
                return $.mage.__('Minimum order value is %amount').replace('%amount', this.getFormattedAmount(INSTALLMENT_MIN_PURCHASE_AMOUNT));
            },
            /**
             * Get formatted message about installment option caption
             *
             * @return {string}
             */
            getCaptionText: function () {
                return CAPTION
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
                return {
                    'kbank'         : 500,
                    'bbl'           : 500,
                    'bay'           : 300,
                    'first_choice'  : 300,
                    'ktc'           : 300,
                    'scb'           : 500,
                    'uob'           : 500
                }[id];
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
                return {
                    'kbank'         : 0.0065,
                    'bbl'           : 0.008,
                    'bay'           : 0.008,
                    'first_choice'  : 0.013,
                    'ktc'           : 0.008,
                    'scb'           : 0.0074,
                    'uob'           : 0.0064
                }[id];
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
                return + window.checkoutConfig.quoteData.grand_total;
            },

            /**
             * Get installment terms
             *
             * @return {string|null}
             */
            getTerms: function () {
                return (
                    this.installmentTermsBBL() ||
                    this.installmentTermsKBank() ||
                    this.installmentTermsFC() ||
                    this.installmentTermsKTC() ||
                    this.installmentTermsBAY() ||
                    this.installmentTermsSCB() ||
                    this.installmentTermsUOB()
                );
            },

            /**
             * Get installment term ko.observable by name
             *
             * @return {string|null}
             */
            getObservableTrem: function (name) {
                switch (name) {
                    case 'installmentTermsUOB':
                        return this.installmentTermsUOB()
                    case 'installmentTermsSCB':
                        return this.installmentTermsSCB()
                    case 'installmentTermsBBL':
                        return this.installmentTermsBBL()
                    case 'installmentTermsKBank':
                        return this.installmentTermsKBank()
                    case 'installmentTermsFC':
                        return this.installmentTermsFC()
                    case 'installmentTermsKTC':
                        return this.installmentTermsKTC()
                    case 'installmentTermsBAY':
                        return this.installmentTermsBAY()
                    default:
                        return null
                }
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
                this.installmentTermsSCB(null);
                this.installmentTermsUOB(null);
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
                return this.getTotal() < INSTALLMENT_MIN_PURCHASE_AMOUNT;
            },

            /**
            * Get a provider list form capabilities api and filter on;y support type
            *
            * @return {Array}
            */
            get_available_providers: function (){
                let _providers = Object.values(window.checkoutConfig.installment_backends);
                return this.providers.filter((a1) => _providers.find(a2 => a1.id === a2._id))
            }

        });
    }
);
