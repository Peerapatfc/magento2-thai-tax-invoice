/**
 * What the buyer typed for a full tax invoice. Shared by the form, the validator and the order request.
 */
define([
    'ko'
], function (ko) {
    'use strict';

    return {
        requested: ko.observable(false),
        name: ko.observable(''),
        taxId: ko.observable(''),
        branch: ko.observable('00000'),
        error: ko.observable('')
    };
});
