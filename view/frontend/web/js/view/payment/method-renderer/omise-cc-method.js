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
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/action/select-payment-method'
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
        quote,
        checkoutData,
        selectPaymentMethodAction
    ) {
        'use strict'

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
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'omise_card_token': this.omiseCardToken(),
                        'omise_card': this.omiseCard(),
                        'omise_save_card': this.omiseSaveCard()
                    }
                }
            },

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
                        'omiseCardNumber',
                        'omiseCardHolderName',
                        'omiseCardExpirationMonth',
                        'omiseCardExpirationYear',
                        'omiseCardSecurityCode',
                        'omiseCardToken',
                        'omiseCard',
                        'omiseSaveCard',
                        'omiseCardError'
                    ])
                this.openOmiseJs()
                return this
            },

            selectPaymentMethod: function () {
                this._super();
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);
                OmiseCard.destroy();
                setTimeout(() => {
                    const element = document.querySelector('.omise-card-form')
                    if(element) {
                        this.applyOmiseJsToElement(this, element)
                    }
                }, 300);
                
                return true
            },

            openOmiseJs: function () {
                ko.bindingHandlers.omiseCardForm = {
                    init: (element) => this.applyOmiseJsToElement(this, element)
                }
            },

            applyOmiseJsToElement: function (self, element) {
                const hideRememberCard = !self.isCustomerLoggedIn()
                const iframeHeightMatching = {
                    '40px': 258,
                    '44px': 270,
                    '48px': 282,
                    '52px': 295,
                }

                const localeMatching = {
                    en_US: 'en',
                    ja_JP: 'ja',
                    th_TH: 'th'
                }

                const { theme, locale, formDesign } = window.checkoutConfig.payment.omise_cc
                const { font, input, checkbox } = formDesign
                let iframeElementHeight = iframeHeightMatching[input.height]
                if (hideRememberCard) {
                    iframeElementHeight = iframeElementHeight - 25
                }
                element.style.height = iframeElementHeight + 'px'

                OmiseCard.configure({
                    publicKey: self.getPublicKey(),
                    element,
                    locale: localeMatching[locale] ?? 'en',
                    customCardForm: true,
                    customCardFormTheme: theme,
                    style: {
                        fontFamily: font.name,
                        fontSize: font.size,
                        input: {
                            height: input.height,
                            borderRadius: input.border_radius,
                            border: `1.2px solid ${input.border_color}`,
                            focusBorder: `1.2px solid ${input.active_border_color}`,
                            background: input.background_color,
                            color: input.text_color,
                            labelColor: input.label_color,
                            placeholderColor: input.placeholder_color,
                        },
                        checkBox: {
                            textColor: checkbox.text_color,
                            themeColor: checkbox.theme_color,
                            border: `1.2px solid ${input.border_color}`,
                        }
                    },
                    customCardFormHideRememberCard: hideRememberCard
                })

                OmiseCard.open({
                    onCreateTokenSuccess: (payload) => {
                        self.createOrder(self, payload)
                    },
                    onError: (err) => {
                        if (err.length > 0) {
                            self.omiseCardError(err.length == 1 ? err[0] : 'Please enter required card information.')
                        }
                        else {
                            self.omiseCardError('Something went wrong. Please refresh the page and try again.')
                        }
                        self.stopPerformingPlaceOrderAction()
                    }
                })
            },

            createOrder: function (self, payload) {
                self.omiseCardToken(payload.token)
                if (payload.remember) {
                    self.omiseSaveCard(payload.remember)
                }
                const failHandler = self.buildFailHandler(this, 300)
                self.getPlaceOrderDeferredObject()
                    .fail(failHandler)
                    .done((order_id) => {
                        let serviceUrl = self.getMagentoReturnUrl(order_id)
                        storage.get(serviceUrl, false)
                            .fail(failHandler)
                            .done(function (response) {
                                if (response) {
                                    if (self.isThreeDSecureEnabled(response))
                                        $.mage.redirect(response.authorize_uri)
                                    else if (self.redirectAfterPlaceOrder) {
                                        redirectOnSuccessAction.execute()
                                    }
                                } else {
                                    failHandler(response)
                                }
                            })
                    })
            },

            /**
             * Is 3-D Secure config enabled
             *
             * @return {boolean}
             */
            isThreeDSecureEnabled: function (response) {
                return !(response.authorize_uri === "")
            },

            /**
             * @return {boolean}
             */
            isCustomerLoggedIn: function () {
                return window.checkoutConfig.payment.omise_cc.isCustomerLoggedIn
            },

            /**
             * @return {boolean}
             */
            hasSavedCards: function () {
                return !!this.getCustomerCards().length
            },

            /**
             * @return {array}
             */
            getCustomerCards: function () {
                return window.checkoutConfig.payment.omise_cc.cards
            },

            /**
             * @return {bool}
             */
            chargeWithNewCard: function (element) {
                $('#payment_form_omise_cc').css({ display: 'block' })
                return true
            },

            /**
             * @return {bool}
             */
            chargeWithSavedCard: function () {
                $('#payment_form_omise_cc').css({ display: 'none' })
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
             * Hook the placeOrder function.
             * Original source: placeOrder(data, event); @ module-checkout/view/frontend/web/js/view/payment/default.js
             *
             * @return {boolean}
             */
            placeOrder: function (data, event) {
                this.omiseCardError(null)
                event && event.preventDefault()

                if (typeof Omise === 'undefined') {
                    alert($.mage.__('Unable to process the payment, loading the external card processing library is failed. Please contact the merchant.'))
                    return false
                }

                let card = this.omiseCard()

                if (card) {
                    this.processOrderWithCard(card)
                    return true
                }

                this.generateTokenWithEmbeddedFormAndPerformPlaceOrderAction()
                return true
            },

            processOrderWithCard: function () {
                const self = this
                const failHandler = this.buildFailHandler(self, 300)

                self.getPlaceOrderDeferredObject()
                    .fail(failHandler)
                    .done(function (order_id) {
                        const serviceUrl = self.getMagentoReturnUrl(order_id)
                        storage.get(serviceUrl, false)
                            .fail(failHandler)
                            .done(function (response) {
                                if (response) {
                                    if (self.isThreeDSecureEnabled(response))
                                        $.mage.redirect(response.authorize_uri)
                                    else if (self.redirectAfterPlaceOrder) {
                                        redirectOnSuccessAction.execute()
                                    }
                                } else {
                                    failHandler(response)
                                }
                            })
                    })
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
        })
    }
)
