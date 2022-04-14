define(
    [
        'ko',
        'Omise_Payment/js/view/payment/omise-offsite-method-renderer',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote'
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
                id: "internet_banking_scb",
                title: 'Siam Commercial Bank',
                code: 'scb',
                logo: 'scb',
                active: true
            },
            {
                id: "internet_banking_ktb",
                title: 'Krungthai Bank',
                code: 'ktb',
                logo: 'ktb',
                active: true
            },
            {
                id: "internet_banking_bay",
                title: 'Krungsri Bank',
                code: 'bay',
                logo: 'bay',
                active: true
            },
            {
                id: "internet_banking_bbl",
                title: 'Bangkok Bank',
                code: 'bbl',
                logo: 'bbl',
                active: true
            },
        ]

        return Component.extend(Base).extend({
            defaults: {
                template: 'Omise_Payment/payment/offsite-internetbanking-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            code: 'omise_offsite_internetbanking',
            restrictedToCurrencies: ['thb'],

            /**
             * Initiate observable fields
             *
             * @return this
             */
            initObservable: function() {
                this._super()
                    .observe([
                        'omiseOffsite'
                    ]);

                return this;
            },

            /**
             * Get a checkout form data
             *
             * @return {Object}
             */
            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'offsite': this.omiseOffsite()
                    }
                };
            },

            /**
            * Get a provider list form capabilities api and filter on;y support type
            *
            * @return {Array}
            */
            get_available_providers: function() {
                let _providers = Object.values(window.checkoutConfig.internet_banking);
                
                return providers.filter((a1) => _providers.find(a2 => a1.id === a2._id))
            }
        });
    }
);
