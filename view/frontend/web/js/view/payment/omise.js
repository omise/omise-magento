define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        const IS_WLB_ENABLED = window.checkoutConfig.omise_wlb_enabled;
        const METHOD_RENDERERS = Object.keys(window.checkoutConfig.omise_payment_list);
        const UPA_FEATURE = window.checkoutConfig.omise_upa_feature;
        METHOD_RENDERERS.forEach(rendererName => {
            let rendererComponent = null;
            if (rendererName == "omise_offsite_installment" && UPA_FEATURE && !IS_WLB_ENABLED) {
                rendererComponent = 'Omise_Payment/js/view/payment/method-renderer/omise-offsite-upa-installment-method';
            } else if (rendererName == "omise_offsite_mobilebanking" && UPA_FEATURE) {
                rendererComponent = 'Omise_Payment/js/view/payment/method-renderer/omise-offsite-upa-mobilebanking-method';
            } else {
                rendererComponent = 'Omise_Payment/js/view/payment/method-renderer/' + rendererName.replace(/_/g, '-') + '-method';
            }
            rendererList.push(
                {
                    type: rendererName,
                    component: rendererComponent
                }
            );
        });

        rendererList.push(
            {
                type: 'omise_offline_conveniencestore',
                component: 'Omise_Payment/js/view/payment/method-renderer/omise-offline-conveniencestore-method'
            }
        );
        return Component.extend({});
    }
);
