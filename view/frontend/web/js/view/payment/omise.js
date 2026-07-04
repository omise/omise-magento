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

        const IS_WLB_ENABLED = window.checkoutConfig.omise_wlb_enable;
        const METHOD_RENDERERS = Object.keys(window.checkoutConfig.omise_payment_list);
        const UPA_FEATURE = window.checkoutConfig.omise_upa_feature;
        METHOD_RENDERERS.forEach(rendererName => {
            if(rendererName == "omise_offsite_installment" && UPA_FEATURE){
                if(!IS_WLB_ENABLED){
                    rendererList.push({
                        type: rendererName,
                        component: 'Omise_Payment/js/view/payment/method-renderer/omise-offsite-upa-installment' + '-method'
                    });    
                }else{
                    rendererList.push({
                        type: rendererName,
                        component: 'Omise_Payment/js/view/payment/method-renderer/' + rendererName.replace(/_/g, '-') + '-method'
                    });
                }
            }else if(rendererName == "omise_offsite_mobilebanking" && UPA_FEATURE){
                rendererList.push({
                    type: rendererName,
                    component: 'Omise_Payment/js/view/payment/method-renderer/omise-offsite-upa-mobilebanking' + '-method'
                });
            }else{
                rendererList.push({
                    type: rendererName,
                    component: 'Omise_Payment/js/view/payment/method-renderer/' + rendererName.replace(/_/g, '-') + '-method'
                });
            }
            
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
