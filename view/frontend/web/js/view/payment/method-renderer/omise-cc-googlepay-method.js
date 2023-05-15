define(
    [
        'ko',
        'Omise_Payment/js/view/payment/omise-base-method-renderer',
        'Magento_Checkout/js/view/payment/default',
        'mage/storage',
        'jquery',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Checkout/js/model/quote',
    ],
    function (
        ko,
        Base,
        Component,
        storage,
        $,
        fullScreenLoader,
        redirectOnSuccessAction,
        quote
    ) {
        'use strict';

        /**
         * Define the version of the Google Pay API referenced when creating configuration.
         *
         * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#PaymentDataRequest|apiVersion in PaymentDataRequest}
         */
        const baseRequest = {
            apiVersion: 2,
            apiVersionMinor: 0,
        };
        
        /**
         * Card networks supported by site and gateway.
         *
         * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#CardParameters|CardParameters}
         */
        const allowedCardNetworks = function() {
            const brandMapping = {
                'American Express': 'AMEX',
                'JCB': 'JCB',
                'MasterCard': 'MASTERCARD',
                'Visa': 'VISA',
            };
            const cardBrands = [];
            window.checkoutConfig.card_brands.forEach(brand => {
                if (brandMapping[brand]) {
                    cardBrands.push(brandMapping[brand])
                }
            });
            return cardBrands;
        }();
        
        /**
         * Card authentication methods supported by site and gateway.
         *
         * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#CardParameters|CardParameters}
         */
        const allowedCardAuthMethods = ["PAN_ONLY"];
        
        /**
         * Identify gateway and site's gateway merchant identifier.
         *
         * The Google Pay API response will return an encrypted payment method capable
         * of being charged by a supported gateway after payer authorization
         *
         * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#gateway|PaymentMethodTokenizationSpecification}
         */
        const tokenizationSpecification = {
            type: 'PAYMENT_GATEWAY',
            parameters: {
                "gateway": "omise",
                "gatewayMerchantId": window.checkoutConfig.payment.omise_cc.publicKey,
            }
        };
        
        /**
         * Describe site's support for the CARD payment method and its required fields.
         *
         * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#CardParameters|CardParameters}
         */
        const baseCardPaymentMethod = {
            type: 'CARD',
            parameters: {
                allowedAuthMethods: allowedCardAuthMethods,
                allowedCardNetworks: allowedCardNetworks,
                billingAddressRequired: (window.checkoutConfig.omise_cc_googlepay.requestBillingAddress === '1'),
                billingAddressParameters: {
                    format: 'FULL',
                    phoneNumberRequired: (window.checkoutConfig.omise_cc_googlepay.requestPhoneNumber === '1'),
                },
            }
        };
        
        /**
         * Describe site's support for the CARD payment method including optional fields
         *
         * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#CardParameters|CardParameters}
         */
        const cardPaymentMethod = Object.assign({},
            baseCardPaymentMethod, {
                tokenizationSpecification: tokenizationSpecification
            }
        );
        
        /**
         * An initialized google.payments.api.PaymentsClient object or null if not yet set
         *
         * @see {@link getGooglePaymentsClient}
         */
        let paymentsClient = null;

        /**
         * UI Class object.
         */
        let klass = null;

        return Component.extend(Base).extend({
            defaults: {
                template: 'Omise_Payment/payment/omise-cc-googlepay-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            code: 'omise_cc_googlepay',

            /**
             * Get a checkout form data.
             *
             * @return {Object}
             */
            getData: function() {
                return {
                    'method': 'omise_cc_googlepay',
                    'additional_data': {
                        'omise_card_token': klass.omiseCardToken(),
                    }
                };
            },

            /**
             * Initiate observable fields.
             *
             * @return this
             */
            initObservable: function() {
                this._super()
                    .observe([
                        'omiseCardToken',
                    ]);

                return this;
            },

            /**
             * Show custom error message.
             */
            displayError: function() {
                document.getElementById('googlepay-error-message').style.display = "block";
            },

            /**
             * Load Google-hosted JavaScript upon landing on the checkout page.
             */
            loadGooglePay: function() {
                klass = this;

                const script = document.createElement('script');
                script.src = "https://pay.google.com/gp/p/js/pay.js";
                script.onload = function() {
                    klass.onGooglePayLoaded();
                };
                document.head.appendChild(script);
                return true;
            },

            /**
             * Initialize Google PaymentsClient after Google-hosted JavaScript has loaded
             *
             * Display a Google Pay payment button after confirmation of the viewer's
             * ability to pay.
             */
            onGooglePayLoaded: function() {
                const client = klass.getGooglePaymentsClient();
                client.isReadyToPay(klass.getGoogleIsReadyToPayRequest())
                    .then(function(response) {
                        if (response.result) {
                            klass.addGooglePayButton();
                            klass.prefetchGooglePaymentData();
                        } else {
                            klass.displayError();
                        }
                    })
                    .catch(function(_err) {
                        klass.displayError();
                    });
            },

            /**
             * Return an active PaymentsClient or initialize.
             *
             * @see {@link https://developers.google.com/pay/api/web/reference/client#PaymentsClient PaymentsClient constructor}
             * @returns {google.payments.api.PaymentsClient} Google Pay API client
             */
            getGooglePaymentsClient: function() {
                if (paymentsClient === null) {
                    paymentsClient = new google.payments.api.PaymentsClient({
                        environment: window.checkoutConfig.isOmiseSandboxOn ? 'TEST' : 'PRODUCTION'
                    });
                }
                return paymentsClient;
            },

            /**
             * Configure site's support for payment methods supported by the Google Pay API.
             *
             * Each member of allowedPaymentMethods should contain only the required fields,
             * allowing reuse of this base request when determining a viewer's ability
             * to pay and later requesting a supported payment method.
             *
             * @returns {object} Google Pay API version, payment methods supported by the site
             */
            getGoogleIsReadyToPayRequest: function() {
                return Object.assign({},
                    baseRequest, {
                        allowedPaymentMethods: [baseCardPaymentMethod]
                    }
                );
            },
            
            /**
             * Add a Google Pay purchase button alongside an existing checkout button.
             *
             * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#ButtonOptions|Button options}
             * @see {@link https://developers.google.com/pay/api/web/guides/brand-guidelines|Google Pay brand guidelines}
             */
            addGooglePayButton: function() {
                const client = klass.getGooglePaymentsClient();
                const button = client.createButton({
                    onClick: klass.onGooglePaymentButtonClicked
                });
                document.getElementById('googlepay-container').appendChild(button);
            },

            /**
             * Prefetch payment data to improve performance.
             *
             * @see {@link https://developers.google.com/pay/api/web/reference/client#prefetchPaymentData|prefetchPaymentData()}
             */
            prefetchGooglePaymentData: function() {
                const paymentDataRequest = klass.getGooglePaymentDataRequest();
                paymentDataRequest.transactionInfo = {
                    totalPriceStatus: 'NOT_CURRENTLY_KNOWN',
                    currencyCode: window.checkoutConfig.quoteData.quote_currency_code.toUpperCase(),
                };
                const client = klass.getGooglePaymentsClient();
                client.prefetchPaymentData(paymentDataRequest);
            },

            /**
             * Configure support for the Google Pay API.
             *
             * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#PaymentDataRequest|PaymentDataRequest}
             * @returns {object} PaymentDataRequest fields
            */
            getGooglePaymentDataRequest: function() {
                const paymentDataRequest = Object.assign({}, baseRequest);
                paymentDataRequest.allowedPaymentMethods = [cardPaymentMethod];
                paymentDataRequest.transactionInfo = klass.getGoogleTransactionInfo();
                paymentDataRequest.merchantInfo = {
                    merchantId: window.checkoutConfig.omise_cc_googlepay.merchantId,
                };
                return paymentDataRequest;
            },
            
            /**
             * Provide Google Pay API with a payment amount, currency, and amount status.
             *
             * @see {@link https://developers.google.com/pay/api/web/reference/request-objects#TransactionInfo|TransactionInfo}
             * @returns {object} transaction info, suitable for use as transactionInfo property of PaymentDataRequest
             */
            getGoogleTransactionInfo: function() {
                return {
                    currencyCode: window.checkoutConfig.quoteData.quote_currency_code.toUpperCase(),
                    totalPriceStatus: 'FINAL',
                    totalPrice: parseFloat(window.checkoutConfig.quoteData.grand_total).toFixed(2)
                };
            },
            
            /**
             * Show Google Pay payment sheet when Google Pay payment button is clicked.
             */
            onGooglePaymentButtonClicked: function() {
                const paymentDataRequest = klass.getGooglePaymentDataRequest();
                paymentDataRequest.transactionInfo = klass.getGoogleTransactionInfo();
            
                const client = klass.getGooglePaymentsClient();
                client.loadPaymentData(paymentDataRequest)
                    .then(function(paymentData) {
                        klass.createOmiseToken(paymentData);
                    })
                    .catch(function(err) {
                        console.error(err);
                    });
            },
            
            /**
             * Create Omise token from the payment data returned by the Google Pay API.
             */
            createOmiseToken: function(paymentData) {
                klass.isPlaceOrderActionAllowed(false);
                fullScreenLoader.startLoader();

                const token = paymentData?.paymentMethodData?.tokenizationData?.token;

                const tokenizationParams = {
                    method: 'googlepay',
                    data: token,
                };

                const billingAddress = (paymentData?.paymentMethodData?.info?.billingAddress);
                if (billingAddress) {
                    Object.assign(tokenizationParams, {
                        billing_name: billingAddress.name,
                        billing_city: billingAddress.locality,
                        billing_country: billingAddress.countryCode,
                        billing_postal_code: billingAddress.postalCode,
                        billing_state: billingAddress.administrativeArea,
                        billing_street1: billingAddress.address1,
                        billing_street2: [billingAddress.address2, billingAddress.address3].filter(s => s).join(' '),
                        billing_phone_number: billingAddress.phoneNumber,
                    });
                }

                Omise.setPublicKey(window.checkoutConfig.payment.omise_cc.publicKey);
                Omise.createToken('tokenization', tokenizationParams, function(statusCode, response) {
                    if (statusCode === 200) {
                        klass.omiseCardToken(response.id);
                        klass.processPayment();
                    } else {
                        klass.isPlaceOrderActionAllowed(true);
                        fullScreenLoader.stopLoader();
                    }
                });
            },

            /**
             * Send charge creation request and finalize payment.
             */
            processPayment: function() {
                const failHandler = klass.buildFailHandler(klass);
                klass.getPlaceOrderDeferredObject()
                    .fail(failHandler)
                    .done(function(orderId) {
                        const serviceUrl = klass.getMagentoReturnUrl(orderId);
                        const storageFailHandler = klass.buildFailHandler(klass);
                        storage.get(serviceUrl, false)
                            .fail(storageFailHandler)
                            .done(function (response) {
                                if (response) {
                                    if (response.authorize_uri !== "") {
                                        $.mage.redirect(response.authorize_uri);
                                    } else {
                                        redirectOnSuccessAction.execute();
                                    }
                                } else {
                                    storageFailHandler(response);
                                }
                            });
                    });
            },
        });
    }
);
