define([
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'mage/storage',
], function (
        $,
        Component,
        placeOrderAction,
        selectPaymentMethodAction,
        checkoutData,
        additionalValidators,
        url,
        storage
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'CoinGate_Merchant/payment/coingate-form'
            },

            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }

                let self = this,
                    placeOrder;

                if (this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                    $.when(placeOrder).fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    }).done(this.afterPlaceOrder.bind(this));

                    return true;
                }
                return false;
            },

            selectPaymentMethod: function () {
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);

                return true;
            },

            afterPlaceOrder: function () {
                storage.post(
                    url.build('rest/V1/coingate/place_order'),
                    false
                ).success(function (response) {
                    if (response.status) {
                        window.location.replace(response.payment_url);
                    } else {
                        window.location.replace('/checkout/cart');
                    }
                });
            }
        });
    }
);
