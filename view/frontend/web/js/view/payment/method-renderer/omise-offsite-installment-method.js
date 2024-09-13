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
            "installment_ktc",
            "installment_first_choice",
            "installment_kbank",
            "installment_bbl",
            "installment_bay",
            "installment_scb",
            "installment_uob",
            "installment_mbb",
            "installment_ttb",
            "installment_wlb_ktc",
            "installment_wlb_first_choice",
            "installment_wlb_kbank",
            "installment_wlb_bbl",
            "installment_wlb_bay",
            "installment_wlb_scb",
            "installment_wlb_uob",
            "installment_wlb_ttb",
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
                        'omiseInstallmentError',
                        'omiseInstallmentToken',
                        'omiseInstallmentSource',
                    ]);

                this.capabilities = checkoutConfig.omise_payment_list[this.code];

                // filter provider for checkout page
                this.providers = this.getAvailableProviders()
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
                element.style.height = '500px';

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
             * Get total amount of an order
             *
             * @return {integer}
             */
            getTotal: function () {
                return + window.checkoutConfig.quoteData.grand_total;
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
            getAvailableProviders: function () {
                const paymentMethods = this.capabilities.map(item => item._id)
                return ko.observableArray(providers.filter(item => paymentMethods.includes(item)))
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
