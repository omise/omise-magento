define(
    [
        'ko',
        'Omise_Payment/js/view/payment/omise-offsite-method-renderer',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
    ],
    function (
        ko,
        Base,
        Component,
        quote
    ) {
        'use strict';
        return Component.extend(Base).extend({
            defaults: {
                template: 'Omise_Payment/payment/offsite-mobilebanking-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            code: 'omise_offsite_mobilebanking',
            restrictedToCurrencies: ['thb'],
            providers: [
                {
                    id: "mobile_banking_kbank",
                    title: 'K PLUS',
                    code: 'kbank',
                    logo: 'kbank',
                    currencies: ['thb'],
                    active: true
                },
                {
                    id: "mobile_banking_scb",
                    title: 'SCB EASY',
                    code: 'scb',
                    logo: 'scb',
                    currencies: ['thb'],
                    active: true
                },
                {
                    id: "mobile_banking_bay",
                    title: 'KMA',
                    code: 'bay',
                    logo: 'bay',
                    currencies: ['thb'],
                    active: true
                },
                {
                    id: "mobile_banking_bbl",
                    title: 'Bualuang mBanking',
                    code: 'bbl',
                    logo: 'bbl',
                    currencies: ['thb'],
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
                        'omiseOffsite'
                    ]);

                // filter provider for checkout page
                this.providers = this.get_available_providers()

                return this;
            },

            /**
             * Is method available to display
             *
             * @return {boolean}
             */
            isAllowCurrency: function (currency) {
                return currency.includes(this.getOrderCurrency())
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
                    }
                };
            },

            /**
            * Get a provider list form capabilities api and filter only support type
            *
            * @return {Array}
            */
            get_available_providers: function () {
                let _providers = Object.values(window.checkoutConfig.mobile_banking);

                return this.providers.filter((a1) => _providers.find(a2 => {
                    if (a1.id === a2._id) {
                        // set currencies from api if is undefined use default value
                        if (a2?.currencies !== null) {
                            a1.currencies = a2.currencies
                        }
                        return true
                    }
                }))
            }

        });
    }
);
