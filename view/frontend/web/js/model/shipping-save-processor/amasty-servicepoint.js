define([
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'mage/storage',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/select-billing-address',
    'uiRegistry',
    'Magento_Checkout/js/model/shipping-save-processor/default',
    'jquery'
], function (
    ko,
    quote,
    resourceUrlManager,
    storage,
    paymentService,
    methodConverter,
    errorProcessor,
    fullScreenLoader,
    selectBillingAddressAction,
    registry,
    defaultProcessor,
    $
) {
    'use_strict';

    return {
        saveShippingInformation: function() {
            var payload;

            var methodComponent = registry.get('checkout.steps.billing-step.payment.payments-list.'+quote.paymentMethod().method+'-form');

            if (quote.shippingAddress().regionId == null || quote.shippingAddress().regionId == '') {
                quote.shippingAddress().regionId = '0';
            }

            if (!quote.billingAddress() || !methodComponent || methodComponent.isAddressSameAsShipping() === true) {
                selectBillingAddressAction(quote.shippingAddress());
            }

            payload = {
                addressInformation: {
                    shipping_address: quote.shippingAddress(),
                    billing_address: quote.billingAddress(),
                    shipping_method_code: quote.shippingMethod().method_code,
                    shipping_carrier_code: quote.shippingMethod().carrier_code
                }
            };

            if (quote.shippingMethod().method_code === 'sendcloud') {
                if (quote.getSendcloudServicePoint()['sendcloud_service_point_id'] > 0) {
                    payload['addressInformation']['extension_attributes'] = quote.getSendcloudServicePoint();
                }
            }

            if (defaultProcessor.hasOwnProperty('extendPayload')) {
                defaultProcessor.extendPayload(payload);
            }

            fullScreenLoader.startLoader();

            return storage.post(
                resourceUrlManager.getUrlForSetShippingInformation(quote),
                JSON.stringify(payload)
            ).done(
                function (response) {
                    quote.setTotals(response.totals);
                    paymentService.setPaymentMethods(methodConverter(response['payment_methods']));
                    fullScreenLoader.stopLoader();
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                    fullScreenLoader.stopLoader();
                }
            );
        }
    };
});
