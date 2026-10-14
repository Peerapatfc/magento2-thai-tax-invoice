define([
    'mage/utils/wrapper',
    'Peerapatfc_ThaiTaxInvoice/js/model/request-assigner'
], function (wrapper, assigner) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            assigner(paymentData);

            return originalAction(paymentData, messageContainer);
        });
    };
});
