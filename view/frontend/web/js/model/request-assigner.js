/**
 * Adds the tax invoice request to the payment data that is sent with the order.
 */
define([
    'Peerapatfc_ThaiTaxInvoice/js/model/tax-invoice'
], function (taxInvoice) {
    'use strict';

    var config = window.checkoutConfig.thaiTaxInvoice || {};

    return function (paymentData) {
        if (!config.enabled || !taxInvoice.requested()) {
            return;
        }

        if (paymentData['extension_attributes'] === undefined) {
            paymentData['extension_attributes'] = {};
        }

        paymentData['extension_attributes']['thai_tax_invoice'] = {
            type: taxInvoice.type(),
            'vat_registered': taxInvoice.vatRegistered(),
            name: taxInvoice.name(),
            'tax_id': taxInvoice.taxId(),
            branch: taxInvoice.vatRegistered() ? taxInvoice.branch() : '',
            address: taxInvoice.sameAddress() ? '' : taxInvoice.address()
        };
    };
});
