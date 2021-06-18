define([
    'jquery',
    'underscore',
    'oroui/js/mediator',
    'orouser/js/views/role-view'
], function($, _, mediator, RoleView) {
    'use strict';

    /**
     * @export orocustomer/js/app/views/customer-role-view
     */
    const CustomerRoleView = RoleView.extend({
        options: {
            customerSelector: ''
        },

        /**
         * @inheritdoc
         */
        constructor: function CustomerRoleView(options) {
            CustomerRoleView.__super__.constructor.call(this, options);
        },

        /**
         * Initialize
         *
         * @param {Object} options
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            CustomerRoleView.__super__.initialize.call(this, options);
        },

        /**
         * @inheritdoc
         */
        getData: function() {
            const data = CustomerRoleView.__super__.getData.call(this);

            data[this.options.formName + '[customer]'] = $(this.options.customerSelector).inputWidget('val');

            return data;
        }
    });

    return CustomerRoleView;
});
