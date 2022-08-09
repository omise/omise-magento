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

        // Duitnow obw is using fpx so wil using the same resource
        const bankList = [
            {
                code: 'affin',
                name: 'Affin Bank',
            },
            {
                code: 'alliance',
                name: 'Alliance Bank',
            },
            {
                code: 'agro',
                name: 'AGRONet',
            },
            {
                code: 'ambank',
                name: 'AmBank',
            },
            {
                code: 'cimb',
                name: 'CIMB Clicks',
            },
            {
                code: 'islam',
                name: 'Bank Islam',
            },
            {
                code: 'rakyat',
                name: 'Bank Rakyat',
            },
            {
                code: 'muamalat',
                name: 'Bank Muamalat',
            },
            {
                code: 'bsn',
                name: 'BSN',
            },
            {
                code: 'hongleong',
                name: 'Hong Leong Bank',
            },
            {
                code: 'hsbc',
                name: 'HSBC Bank',
            },
            {
                code: 'kfh',
                name: 'KFH',
            },
            {
                code: 'maybank2u',
                name: 'Maybank2U',
            },
            {
                code: 'ocbc',
                name: 'OCBC Bank',
            },
            {
                code: 'publicBank',
                name: 'Public Bank',
            },
            {
                code: 'rhb',
                name: 'RHB Bank',
            },
            {
                code: 'sc',
                name: 'Standard Chartered',
            },
            {
                code: 'uob',
                name: 'UOB Bank',
            },
        ]

        return Component.extend(Base).extend({
            defaults: {
                template: 'Omise_Payment/payment/offsite-duitnowobw-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            code: 'omise_offsite_duitnowobw',
            restrictedToCurrencies: ['myr'],
            logo: {
                file: "images/duitnow_obw.png",
                width: "65",
                height: "35",
                name: "duitnow_obw"
            },
            banks: ko.observable(bankList),
            selectedDuitnowOBWBank: ko.observable(),
            bankLabel: function(name) {
                return name;
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
                        'bank': this.selectedDuitnowOBWBank()
                    }
                };
            },
        });
    }
);
