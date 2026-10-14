/**
 * What the buyer typed for a full tax invoice. Shared by the form, the validator and the order request.
 */
define([
    'ko'
], function (ko) {
    'use strict';

    return {
        requested: ko.observable(false),
        type: ko.observable('company'),
        vatRegistered: ko.observable(true),
        sameAddress: ko.observable(true),
        address: ko.observable(''),
        name: ko.observable(''),
        taxId: ko.observable(''),
        branch: ko.observable('00000'),
        error: ko.observable('')
    };
});
