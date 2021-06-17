define(function(require) {
    'use strict';

    const BaseComponent = require('oroui/js/app/components/base/component');
    const _ = require('underscore');
    const routing = require('routing');
    const widgetManager = require('oroui/js/widget-manager');

    const CustomerUser = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            widgetAlias: null,
            customerFormId: null,
            customerUserId: null
        },

        /**
         * @inheritDoc
         */
        constructor: function CustomerUser(options) {
            CustomerUser.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            this.options._sourceElement
                .on('change', this.options.customerFormId, this.reloadRoleWidget.bind(this));
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.options._sourceElement.off('change');
            CustomerUser.__super__.dispose.call(this);
        },

        /**
         * Reload widget with roles
         *
         * @param {Event} e
         */
        reloadRoleWidget: function(e) {
            const customerUserId = this.options.customerUserId;
            const customerId = e.target.value;

            widgetManager.getWidgetInstanceByAlias(this.options.widgetAlias, function(widget) {
                const params = {customerId: customerId};
                if (customerUserId) {
                    params.customerUserId = customerUserId;
                }

                widget.setUrl(
                    routing.generate('oro_customer_customer_user_roles', params)
                );
                widget.render();
            });
        }
    });

    return CustomerUser;
});
