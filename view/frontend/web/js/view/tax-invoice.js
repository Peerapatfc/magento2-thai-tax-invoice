/**
 * "I want a full tax invoice" on the payment step: name, tax ID and branch of the buyer.
 * The address on the invoice is the billing address.
 */
define([
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/additional-validators',
    'mage/translate',
    'Peerapatfc_ThaiTaxInvoice/js/model/tax-invoice',
    'Peerapatfc_ThaiTaxInvoice/js/model/tax-identity'
], function (Component, quote, additionalValidators, $t, taxInvoice, identity) {
    'use strict';

    var config = window.checkoutConfig.thaiTaxInvoice || {};

    additionalValidators.registerValidator({
        /**
         * @return {Boolean}
         */
        validate: function () {
            var problem = null;

            if (!config.enabled || !taxInvoice.requested()) {
                return true;
            }

            if (!taxInvoice.name().trim()) {
                problem = $t('Enter the name for the tax invoice.');
            } else if (!identity.isValidTaxId(taxInvoice.taxId())) {
                problem = $t('The tax ID must be 13 digits with a correct check digit.');
            } else if (!identity.isValidBranch(taxInvoice.branch())) {
                problem = $t('The branch code must be 5 digits. Use 00000 for a head office.');
            }

            taxInvoice.error(problem || '');

            return !problem;
        }
    });

    return Component.extend({
        defaults: {
            template: 'Peerapatfc_ThaiTaxInvoice/tax-invoice'
        },
        isEnabled: !!config.enabled,
        requested: taxInvoice.requested,
        buyerName: taxInvoice.name,
        taxId: taxInvoice.taxId,
        branch: taxInvoice.branch,
        error: taxInvoice.error,

        /**
         * Fills in the name from the billing address the first time the box is ticked.
         *
         * @return {Boolean}
         */
        onToggle: function () {
            var billing = quote.billingAddress();

            if (taxInvoice.requested() && !taxInvoice.name() && billing) {
                taxInvoice.name(billing.company || [billing.firstname, billing.lastname].join(' ').trim());
            }

            return true;
        }
    });
});
