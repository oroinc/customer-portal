define(function(require) {
    'use strict';

    const $ = require('jquery.validate');

    $.validator.loadMethod('orocustomer/js/validator/unique-customer-user-name-and-email');
});
