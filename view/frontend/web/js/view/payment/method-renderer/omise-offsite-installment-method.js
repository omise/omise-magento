define(
    [
        'jquery',
        'ko',
        'Omise_Payment/js/view/payment/omise-offsite-method-renderer',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/storage',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/action/select-payment-method'
    ],
    function (
        $,
        ko,
        Base,
        Component,
        quote,
        priceUtils,
        fullScreenLoader,
        storage,
        checkoutData,
        selectPaymentMethodAction
    ) {
        'use strict';
        const providers = [
            {
                id: "installment_ktc",
                title: $.mage.__('Krungthai Card'),
                code: 'ktc',
                logo: 'ktc',
                active: true
            },
            {
                id: "installment_first_choice",
                title: $.mage.__('First Choice'),
                code: 'first_choice',
                logo: 'fc',
                active: true
            },
            {
                id: "installment_kbank",
                title: $.mage.__('Kasikorn Bank'),
                code: 'kbank',
                logo: 'kbank',
                active: true
            },
            {
                id: "installment_bbl",
                title: $.mage.__('Bangkok Bank'),
                code: 'bbl',
                logo: 'bbl',
                active: true
            },
            {
                id: "installment_bay",
                title: $.mage.__('Krungsri'),
                code: 'bay',
                logo: 'bay',
                active: true
            },
            {
                id: "installment_scb",
                title: $.mage.__('Siam Commercial Bank'),
                code: 'scb',
                logo: 'scb',
                active: true
            },
            {
                id: "installment_uob",
                title: $.mage.__('United Overseas Bank'),
                code: 'uob',
                logo: 'uob',
                active: true
            },
            {
                id: "installment_mbb",
                title: $.mage.__('MayBank'),
                code: 'mbb',
                logo: 'mbb',
                active: true
            },
            {
                id: "installment_ttb",
                title: $.mage.__('TMBThanachart Bank'),
                code: 'ttb',
                logo: 'ttb',
                active: true
            },

            {
                id: "installment_wlb_ktc",
                title: $.mage.__('Krungthai Card'),
                code: 'ktc',
                logo: 'ktc',
                active: true
            },
            {
                id: "installment_wlb_first_choice",
                title: $.mage.__('First Choice'),
                code: 'first_choice',
                logo: 'fc',
                active: true
            },
            {
                id: "installment_wlb_kbank",
                title: $.mage.__('Kasikorn Bank'),
                code: 'kbank',
                logo: 'kbank',
                active: true
            },
            {
                id: "installment_wlb_bbl",
                title: $.mage.__('Bangkok Bank'),
                code: 'bbl',
                logo: 'bbl',
                active: true
            },
            {
                id: "installment_wlb_bay",
                title: $.mage.__('Krungsri'),
                code: 'bay',
                logo: 'bay',
                active: true
            },
            {
                id: "installment_wlb_scb",
                title: $.mage.__('Siam Commercial Bank'),
                code: 'scb',
                logo: 'scb',
                active: true
            },
            {
                id: "installment_wlb_uob",
                title: $.mage.__('United Overseas Bank'),
                code: 'uob',
                logo: 'uob',
                active: true
            },
            {
                id: "installment_wlb_ttb",
                title: $.mage.__('TMBThanachart Bank'),
                code: 'ttb',
                logo: 'ttb',
                active: true
            },
        ];

        function convertToCents(dollarAmount) {
            return Math.round(parseFloat(dollarAmount) * 100);
        }

        return Component.extend(Base).extend({
            defaults: {
                template: 'Omise_Payment/payment/offsite-installment-form'
            },
            code: 'omise_offsite_installment',
            restrictedToCurrencies: ['thb', 'myr'],
            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),
            capabilities: null,
            billingAddressCountries: ["US", "GB", "CA"],

            /**
             * Get Omise public key
             *
             * @return {string}
             */
            getPublicKey: function () {
                return window.checkoutConfig.payment.omise_cc.publicKey
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
                        'installmentTermsSCB',
                        'installmentTermsUOB',
                        'installmentTermsMBB',
                        'installmentTermsTTB',
                        'omiseInstallmentError',
                        'omiseInstallmentToken',
                        'omiseInstallmentSource',
                    ]);

                this.capabilities = checkoutConfig.omise_payment_list[this.code];

                // filter provider for checkout page
                this.providers = this.get_available_providers()
                this.openOmiseJs();
                return this;
            },

            selectPaymentMethod: function () {
                this._super();
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);
                OmiseCard.destroy();
                setTimeout(() => {
                    const element = document.querySelector('.omise-installment-form')
                    if(element) {
                        this.applyOmiseJsToElement(this, element);
                    }
                }, 300);
                return true
            },

            openOmiseJs: function () {
                ko.bindingHandlers.omiseInstallmentForm = {
                    init: (element) => this.applyOmiseJsToElement(this, element)
                }
            },

            applyOmiseJsToElement: function (self, element) {
                const localeMatching = {
                    en_US: 'en',
                    ja_JP: 'ja',
                    th_TH: 'th'
                }

                const { locale } = window.checkoutConfig.payment.omise_cc
                element.style.height = 500 + 'px';

                OmiseCard.configure({
                    publicKey: self.getPublicKey(),
                    amount: convertToCents(quote.totals().grand_total),
                    element,
                    iframeAppId: 'omise-checkout-installment-form',
                    customCardForm: false,
                    customInstallmentForm: true,
                    locale: localeMatching[locale] ?? 'en',
                    defaultPaymentMethod: 'installment'
                });
                
                OmiseCard.open({
                    onCreateSuccess: (payload) => {
                        self.createOrder(self, payload)
                    },
                    onError: (err) => {
                        if (err.length > 0) {
                            self.omiseInstallmentError(err.length == 1 ? err[0] : 'Please enter required card information.')
                        }
                        else {
                            self.omiseInstallmentError('Something went wrong. Please refresh the page and try again.')
                        }
                        self.stopPerformingPlaceOrderAction()
                    }
                });
            },

            createOrder: function (self, payload) {
                self.omiseInstallmentToken(payload.token)
                self.omiseInstallmentSource(payload.source)
                const failHandler = self.buildFailHandler(this, 300)
                self.getPlaceOrderDeferredObject()
                    .fail(failHandler)
                    .done((order_id) => {
                        let serviceUrl = self.getMagentoReturnUrl(order_id)
                        storage.get(serviceUrl, false)
                            .fail(failHandler)
                            .done(function (response) {
                                if (response) {
                                    $.mage.redirect(response.authorize_uri)
                                } else {
                                    failHandler(response)
                                }
                            })
                    })
            },

            /**
             * Hook the placeOrder function.
             * Original source: placeOrder(data, event); @ module-checkout/view/frontend/web/js/view/payment/default.js
             *
             * @return {boolean}
             */
            placeOrder: function (data, event) {
                this.omiseInstallmentError(null)
                event && event.preventDefault()

                if (typeof Omise === 'undefined') {
                    alert($.mage.__('Unable to process the payment, loading the external card processing library is failed. Please contact the merchant.'))
                    return false
                }

                this.generateTokenWithEmbeddedFormAndPerformPlaceOrderAction()
                return true
            },

            /**
             * Generate Omise token with embedded form before proceed the placeOrder process.
             *
             * @return {void}
             */
            generateTokenWithEmbeddedFormAndPerformPlaceOrderAction: function () {
                this.startPerformingPlaceOrderAction()
                let billingAddress = {}
                let selectedBillingAddress = quote.billingAddress()
                if (this.billingAddressCountries.indexOf(selectedBillingAddress.countryId) > -1) {
                    Object.assign(billingAddress, this.getSelectedTokenBillingAddress(selectedBillingAddress))
                }
                OmiseCard.requestCardToken(billingAddress)
            },

            /**
             * Get installment min amount from capability
             *
             * @returns {number}
             */
            getInstallmentMinLimit: function () {
                return checkoutConfig.omise_installment_min_limit;
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
                return $.mage.__('Minimum order value is %amount').replace('%amount', this.getFormattedAmount(this.getInstallmentMinLimit()));
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
                    'kbank': 300,
                    'bbl': 500,
                    'bay': 500,
                    'first_choice': 300,
                    'ktc': 300,
                    'scb': 500,
                    'uob': 500,
                    'mbb': 83.33,
                    'ttb': 500,
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
                    'kbank': 0.0065,
                    'bbl': 0.0074,
                    'bay': 0.0074,
                    'first_choice': 0.0116,
                    'ktc': 0.0074,
                    'scb': 0.0074,
                    'uob': 0.0064,
                    'mbb': 0,
                    'ttb': 0.008,
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
                    this.installmentTermsUOB() ||
                    this.installmentTermsMBB() ||
                    this.installmentTermsTTB()
                );
            },

            /**
             * Get installment term ko.observable by name
             *
             * @return {string|null}
             */
            getObservableTerm: function (name) {
                switch (name) {
                    case 'installment_uob':
                        return this.observe().installmentTermsUOB
                    case 'installment_scb':
                        return this.observe().installmentTermsSCB
                    case 'installment_bbl':
                        return this.observe().installmentTermsBBL
                    case 'installment_kbank':
                        return this.observe().installmentTermsKBank
                    case 'installment_first_choice':
                        return this.observe().installmentTermsFC
                    case 'installment_ktc':
                        return this.observe().installmentTermsKTC
                    case 'installment_bay':
                        return this.observe().installmentTermsBAY
                    case 'installment_mbb':
                        return this.observe().installmentTermsMBB
                    case 'installment_ttb':
                        return this.observe().installmentTermsTTB
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
                this.installmentTermsMBB(null);
                this.installmentTermsTTB(null);
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
                        'card': this.omiseInstallmentToken(),
                        'source': this.omiseInstallmentSource()
                    }
                };
            },

            /**
             * Returns Installment Terms
             * @param {string} id - Bank id
             * @return {array}
             */
            getInstallmentTerms: function (id) {
                const installmentBackends = this.capabilities;
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
                return this.getTotal() < this.getInstallmentMinLimit();
            },

            /**
            * Get a provider list form capabilities api ,setup observer by id and filter only support type
            *
            * @return {Array}
            */
            get_available_providers: function () {
                let _providers = Object.values(this.capabilities);

                return ko.observableArray(providers.filter((a1) => _providers.find(a2 => {
                    if (a1.id === a2._id) {
                        a1.obs = this.getInstallmentTerms(a2._id)
                        return true
                    }
                }
                )))
            },

            /**
             * Start performing place order action,
             * by disable a place order button and show full screen loader component.
             */
            startPerformingPlaceOrderAction: function () {
                this.isPlaceOrderActionAllowed(false)
                fullScreenLoader.startLoader()
            },

            /**
             * Stop performing place order action,
             * by disable a place order button and show full screen loader component.
             */
            stopPerformingPlaceOrderAction: function () {
                fullScreenLoader.stopLoader()
                this.isPlaceOrderActionAllowed(true)
            },

            getSelectedTokenBillingAddress: function (selectedBillingAddress) {
                let address = {
                    state: selectedBillingAddress.region,
                    postal_code: selectedBillingAddress.postcode,
                    phone_number: selectedBillingAddress.telephone,
                    country: selectedBillingAddress.countryId,
                    city: selectedBillingAddress.city,
                    street1: selectedBillingAddress.street[0]
                }

                if (selectedBillingAddress.street[1]) {
                    address.street2 = selectedBillingAddress.street[1]
                }

                return address
            }
        });
    }
);
