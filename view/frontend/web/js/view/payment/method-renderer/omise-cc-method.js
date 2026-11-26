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
        'Magento_Checkout/js/checkout-data'
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
        checkoutData
    ) {
        'use strict'

        return Component.extend(Base).extend({
            defaults: {
                template: 'Omise_Payment/payment/omise-cc-form'
            },
            redirectAfterPlaceOrder: true,
            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),
            orderRequestSent: false,
            cardFormElement: null,
            cardFormInitialized: false,
            cardFormWaitingForScript: false,
            cardFormSelected: false,
            cardFormRetryTimer: null,
            cardFormAutoRetryAttempted: false,
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
                checkoutData.setSelectedPaymentMethod(this.item.method);
                if (this.cardFormRetryTimer) {
                    window.clearTimeout(this.cardFormRetryTimer)
                    this.cardFormRetryTimer = null
                }
                window.setTimeout(() => {
                    this.reopenOmiseCardForm(this.cardFormElement, 'radio selection')
                }, 300)
                return true
            },

            openOmiseJs: function () {
                const self = this
                ko.bindingHandlers.omiseCardForm = {
                    init: function (element) {
                        self.cardFormElement = element
                    },
                    update: function (element, valueAccessor) {
                        self.cardFormSelected = !!ko.unwrap(valueAccessor())
                        if (self.cardFormSelected) {
                            self.initializeOmiseCardForm(element)
                        } else if (self.cardFormInitialized && window.OmiseCard) {
                            if (self.cardFormRetryTimer) {
                                window.clearTimeout(self.cardFormRetryTimer)
                                self.cardFormRetryTimer = null
                            }
                            window.OmiseCard.destroy()
                            self.cardFormInitialized = false
                            self.cardFormAutoRetryAttempted = false
                        }
                    }
                }
            },

            initializeOmiseCardForm: function (element) {
                if (this.cardFormInitialized || this.cardFormElement !== element || !this.cardFormSelected) {
                    return
                }

                if (!window.OmiseCard) {
                    if (!this.cardFormWaitingForScript) {
                        const script = document.querySelector('script[src*="omise.js"]')
                        if (script) {
                            console.warn('[Omise CC] OmiseCard is not ready; waiting for script load', {
                                scriptSrc: script.src,
                                scriptReadyState: script.readyState || 'not exposed'
                            });
                            this.cardFormWaitingForScript = true
                            script.addEventListener('load', () => {
                                this.cardFormWaitingForScript = false
                                this.initializeOmiseCardForm(element)
                            }, { once: true })
                            script.addEventListener('error', (event) => {
                                this.cardFormWaitingForScript = false
                                console.error('[Omise CC] Omise script failed to load', event)
                            }, { once: true })
                        } else {
                            console.error('[Omise CC] OmiseCard is unavailable and no omise.js script element was found')
                        }
                    }
                    return
                }

                try {
                    this.applyOmiseJsToElement(this, element)
                    this.cardFormInitialized = true
                    this.scheduleCardFormRetry(element)
                } catch (error) {
                    console.error('[Omise CC] Form initialization threw an error', error)
                }
            },

            scheduleCardFormRetry: function (element) {
                if (this.cardFormAutoRetryAttempted || this.cardFormRetryTimer) {
                    return
                }

                this.cardFormRetryTimer = window.setTimeout(() => {
                    this.cardFormRetryTimer = null
                    if (!this.cardFormSelected || !element.isConnected || element.querySelector('iframe')) {
                        return
                    }

                    this.cardFormAutoRetryAttempted = true
                    this.reopenOmiseCardForm(element, 'automatic mount retry')
                }, 500)
            },

            reopenOmiseCardForm: function (element, reason) {
                if (!element || !element.isConnected || !this.cardFormSelected || !window.OmiseCard) {
                    return
                }
                if (element.querySelector('iframe')) {
                    return
                }

                console.warn('[Omise CC] Reopening Omise form', { reason })
                window.OmiseCard.destroy()
                this.cardFormInitialized = false
                try {
                    this.applyOmiseJsToElement(this, element)
                    this.cardFormInitialized = true
                } catch (error) {
                    console.error('[Omise CC] Reopen failed', { reason, error })
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
                const supportedCardIcons = window.checkoutConfig.payment.omise_cc.supported_card_icons
                const { font, input, checkbox } = formDesign
                let iframeElementHeight = iframeHeightMatching[input.height]
                if (hideRememberCard) {
                    iframeElementHeight = iframeElementHeight - 25
                }
                element.style.height = iframeElementHeight + 'px'

                const cardFormConfig = {
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
                }

                if (Array.isArray(supportedCardIcons) && supportedCardIcons.length > 0) {
                    cardFormConfig.customCardFormBrandIcons = supportedCardIcons
                    cardFormConfig.cardBrands = supportedCardIcons.join(' ')
                }

                OmiseCard.configure(cardFormConfig)

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
                        self.orderRequestSent = false
                        self.stopPerformingPlaceOrderAction()
                    }
                })
            },

            createOrder: function (self, payload) {
                if (self.orderRequestSent) {
                    return
                }
                self.orderRequestSent = true;

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
                const selectedBillingAddress = quote.billingAddress()
                const tokenData = {
                    email: quote.guestEmail ?? window.checkoutConfig.customerData.email,
                    billingAddress: null
                }
                if (this.billingAddressCountries.indexOf(selectedBillingAddress.countryId) > -1) {
                    tokenData['billingAddress'] = this.getSelectedTokenBillingAddress(selectedBillingAddress)
                }
                OmiseCard.requestCardToken(tokenData)
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
