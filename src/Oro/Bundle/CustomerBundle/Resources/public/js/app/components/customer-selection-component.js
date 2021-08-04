define(function(require) {
    'use strict';

    const BaseComponent = require('oroui/js/app/components/base/component');
    const LoadingMaskView = require('oroui/js/app/views/loading-mask-view');
    const $ = require('jquery');
    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const routing = require('routing');
    const mediator = require('oroui/js/mediator');

    const CustomerSelectionComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            customerSelect: '.customer-customer-select input[type="hidden"]',
            customerUserSelect: '.customer-customeruser-select input[type="hidden"]',
            customerUserMultiSelect: '.customer-customeruser-multiselect input[type="hidden"]',
            customerRoute: 'oro_customer_customer_user_get_customer',
            errorMessage: 'Sorry, an unexpected error has occurred.'
        },

        /**
         * @property {Object}
         */
        $customerSelect: null,

        /**
         * @property {Object}
         */
        $customerUserSelect: null,

        /**
         * @property {Object}
         */
        $customerUserMultiSelect: null,

        /**
         * @property {LoadingMaskView|null}
         */
        loadingMask: null,

        /**
         * @inheritdoc
         */
        constructor: function CustomerSelectionComponent(options) {
            CustomerSelectionComponent.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            this.$el = options._sourceElement;
            this.loadingMask = new LoadingMaskView({container: this.$el});

            this.$customerSelect = this.$el.find(this.options.customerSelect);
            this.$customerUserSelect = this.$el.find(this.options.customerUserSelect);
            this.$customerUserMultiSelect = this.$el.find(this.options.customerUserMultiSelect);

            this.$el
                .on('change', this.options.customerSelect, this.onCustomerChanged.bind(this))
                .on('change', this.options.customerUserSelect, this.onCustomerUserChanged.bind(this))
                .on('change', this.options.customerUserMultiSelect, this.onCustomerUserChanged.bind(this))
            ;

            this.updateCustomerUserSelectData({customer_id: this.$customerSelect.val()});
        },

        /**
         * Handle Customer change
         */
        onCustomerChanged: function() {
            this.$customerUserSelect.inputWidget('val', '');
            this.$customerUserMultiSelect.inputWidget('val', '');

            this.updateCustomerUserSelectData({customer_id: this.$customerSelect.val()});
            this.triggerChangeCustomerUserEvent();
        },

        /**
         * Handle CustomerUser change
         *
         * @param {jQuery.Event} e
         */
        onCustomerUserChanged: function(e) {
            const customerId = this.$customerSelect.val();
            const customerUserId = $(e.target).val();

            if (customerId || !customerUserId) {
                this.triggerChangeCustomerUserEvent();

                return;
            }

            const self = this;
            $.ajax({
                url: routing.generate(this.options.customerRoute, {id: customerUserId}),
                type: 'GET',
                beforeSend: function() {
                    self.loadingMask.show();
                },
                success: function(response) {
                    self.$customerSelect.inputWidget('val', response.customerId || '');

                    self.updateCustomerUserSelectData({customer_id: response.customerId});
                    self.triggerChangeCustomerUserEvent();
                },
                complete: function() {
                    self.loadingMask.hide();
                },
                errorHandlerMessage: __(this.options.errorMessage)
            });
        },

        /**
         * @param {Object} data
         */
        updateCustomerUserSelectData: function(data) {
            this.$customerUserSelect.data('select2_query_additional_params', data);
            this.$customerUserMultiSelect.data('select2_query_additional_params', data);
        },

        triggerChangeCustomerUserEvent: function() {
            mediator.trigger('customer-customer-user:change', {
                customerId: this.$customerSelect.val(),
                customerUserId: this.$customerUserSelect.val()
            });
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$el.off();

            CustomerSelectionComponent.__super__.dispose.call(this);
        }
    });

    return CustomerSelectionComponent;
});
