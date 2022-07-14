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
                template: 'Omise_Payment/payment/offsite-alipayplus-form'
            },

            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

            code: 'omise_offsite_touchngo',
            restrictedToCurrencies: ['sgd', 'myr'],

            initialize: function () {
                this._super(); //_super will call parent's `initialize` method here
                return this;
            },

            
            /**
            * Get payment method title
            *
            * @return {string}
            */
            getTitle: function () {
                let provider = checkoutConfig.omise_payment_list['omise_offsite_touchngo'][0]['provider']
                return this._super() + (provider == 'Alipay_plus' ? '(Alipay + Partner)' : '');
            },
        });
        
    }
);
