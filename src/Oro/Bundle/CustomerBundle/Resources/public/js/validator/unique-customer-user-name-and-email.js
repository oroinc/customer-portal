/* global define */
define([
    'underscore', 'orotranslation/js/translator', 'jquery', 'routing'
], function(_, __, $, routing) {
    'use strict';

    const method = 'Oro\\Bundle\\CustomerBundle\\Validator\\Constraints\\UniqueCustomerUserNameAndEmail';
    const validationUrl = routing.generate('oro_customer_frontend_customer_user_validate');
    const defaultParam = {
        message: 'oro.customer.message.user_customer_exists'
    };

    /**
     * @export orocustomer/js/validator/unique-customer-user-name-and-email
     */
    return [
        method,
        function(value, element, param) {
            const previous = this.previousValue(element, method);
            const validator = this;

            if (previous.old === value) {
                return previous.valid;
            }

            validator.startRequest(element);
            $.ajax(
                $.extend(true, {
                    url: validationUrl,
                    data: {value: value},
                    method: 'post',

                    mode: 'abort',
                    port: 'validate' + element.name,
                    dataType: 'json',
                    context: validator.currentForm
                }, param)
            ).done(function(data) {
                const valid = data.valid;

                if (valid) {
                    const submitted = validator.formSubmitted;

                    validator.resetInternals();
                    validator.toHide = validator.errorsFor(element);
                    validator.formSubmitted = submitted;
                    validator.successList.push(element);
                    validator.invalid[element.name] = false;
                    validator.showErrors();
                } else {
                    const errors = {};
                    const message = validator.defaultMessage(element, {method: method, parameters: value});

                    errors[element.name] = previous.message = message;
                    validator.invalid[element.name] = true;
                    validator.showErrors(errors);
                }

                previous.old = value;
                previous.valid = valid;

                validator.stopRequest(element, valid);
            }).always(function() {
                validator.stopRequest(element, false);
            });

            return 'pending';
        },
        function(param, element) {
            param = _.extend({}, defaultParam, param);

            return __(param.message);
        }
    ];
});
