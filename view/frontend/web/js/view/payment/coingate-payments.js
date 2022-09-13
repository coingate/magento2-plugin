define([
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ], function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'coingate_merchant',
                component: 'CoinGate_Merchant/js/view/payment/method-renderer/coingate-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
