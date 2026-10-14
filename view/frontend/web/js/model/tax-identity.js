/**
 * Thai tax ID and branch checks, the same rules as the server (Model/TaxIdentity.php).
 */
define([], function () {
    'use strict';

    var THAI_DIGITS = '๐๑๒๓๔๕๖๗๘๙';

    /**
     * @param {String} value
     * @return {String} Arabic digits only, with spaces and dashes removed
     */
    function normalize(value) {
        return String(value || '')
            .replace(/[๐-๙]/g, function (digit) {
                return String(THAI_DIGITS.indexOf(digit));
            })
            .replace(/[\s-]/g, '');
    }

    return {
        normalize: normalize,

        /**
         * @param {String} value
         * @return {Boolean} 13 digits with a correct check digit
         */
        isValidTaxId: function (value) {
            var taxId = normalize(value),
                sum = 0,
                i;

            if (!/^\d{13}$/.test(taxId)) {
                return false;
            }

            for (i = 0; i < 12; i++) {
                sum += Number(taxId.charAt(i)) * (13 - i);
            }

            return (11 - sum % 11) % 10 === Number(taxId.charAt(12));
        },

        /**
         * @param {String} value
         * @return {Boolean} 5 digits
         */
        isValidBranch: function (value) {
            return /^\d{5}$/.test(normalize(value));
        }
    };
});
