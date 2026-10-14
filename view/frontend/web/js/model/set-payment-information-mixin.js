define([
    'mage/utils/wrapper',
    'Peerapatfc_ThaiTaxInvoice/js/model/request-assigner'
], function (wrapper, assigner) {
    'use strict';

    return function (setPaymentInformationAction) {
        return wrapper.wrap(setPaymentInformationAction, function (originalAction, messageContainer, paymentData) {
            assigner(paymentData);

            return originalAction(messageContainer, paymentData);
        });
    };
});
