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

        function checkWlbStatus(omise_payment_list) {
            const methods = omise_payment_list?.omise_offsite_installment || [];

            return methods.some(method => 
                method.name.startsWith('installment_wlb')
            ) ? 1 : 0;
        }

        const omisePaymentList = window.checkoutConfig.omise_payment_list;
        var is_wlb = checkWlbStatus(omisePaymentList);

        const METHOD_RENDERERS = Object.keys(window.checkoutConfig.omise_payment_list);
        const UPA_FEATURE = window.checkoutConfig.omise_upa_feature;
        METHOD_RENDERERS.forEach(rendererName => {
            if(rendererName == "omise_offsite_installment" && UPA_FEATURE){
                if(!is_wlb){
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
