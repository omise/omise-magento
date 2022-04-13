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

        const providers = [
            {
                id: "mobile_banking_kbank",
                title: 'K PLUS',
                logo: 'kbank',
                currencies: ['thb'],
                active : false
            },
            {
                id: "mobile_banking_scb",
                title: 'SCB EASY',
                logo: 'scb',
                currencies: ['thb'],
                active: true
            },
            {
                id: "mobile_banking_bay",
                title: 'KMA',
                logo: 'bay',
                currencies: ['thb'],
                active: false
            },
            {
                id: "mobile_banking_ocbc_pao",
                title: 'OCBC Pay Anyone',
                logo: 'ocbc_pao',
                currencies: ['sgd'],
                active: true
            },
        ]

        return Component.extend(Base).extend({
            defaults: {
                template: 'Omise_Payment/payment/offsite-mobilebanking-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            code: 'omise_offsite_mobilebanking',
            restrictedToCurrencies: ['thb', 'sgd'],
            
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
            * Get a provider list form capabilities api and filter on;y support type
            *
            * @return {Array}
            */
            get_available_providers: function () {
                let _providers = Object.values(window.checkoutConfig.mobile_banking);

                return providers.filter((a1) => _providers.find(a2 => a1.id === a2._id))
            }

        });
    }
);
