define(function(require) {
    'use strict';

    var CustomerUserRoleComponent;
    var BaseComponent = require('oroui/js/app/components/base/component');
    var mediator = require('oroui/js/mediator');
    var $ = require('jquery');
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var Modal = require('oroui/js/modal');

    CustomerUserRoleComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            customerFieldId: '#customerFieldId',
            datagridName: 'customer-users-datagrid',
            originalValue: null,
            previousValueDataAttribute: 'previousValue',
            enableConfirmation: false,
            dialogOptions: {
                title: __('oro.customer.customer_user_role.change_customer_confirmation_title'),
                okText: __('oro.customer.customer_user_role.continue'),
                cancelText: __('oro.customer.customer_user_role.cancel'),
                content: __('oro.customer.customer_user_role.content')
            }
        },

        /**
         * @property {jQuery.Element}
         */
        customerField: null,

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options);

            this.customerField = this.options._sourceElement.find(this.options.customerFieldId);
            this.customerField.data(this.options.previousValueDataAttribute, this.options.originalValue);

            this.options._sourceElement
                .on('change', this.options.customerFieldId, _.bind(this.onCustomerSelectorChange, this));
        },

        /**
         * @param {jQuery.Event} e
         */
        onCustomerSelectorChange: function(e) {
            var value = e.target.value;

            if (value === this.options.originalValue || !this.options.enableConfirmation) {
                this._updateGridAndSaveParameters(value);
                return;
            }

            this._getCustomerConfirmDialog(
                function() {
                    this._updateGridAndSaveParameters(value);
                },
                function() {
                    this.customerField
                        .inputWidget('val', this.customerField.data(this.options.previousValueDataAttribute));
                    this.customerField.data(this.options.previousValueDataAttribute, this.options.originalValue);
                }
            );
        },

        /**
         * @param {String} value
         * @private
         */
        _updateGridAndSaveParameters: function(value) {
            this._updateCustomerUserGrid(value);
            this.customerField.data(this.options.previousValueDataAttribute, value);
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            delete this.customerField;

            this.options._sourceElement.off('change');

            if (this.changeCustomerConfirmDialog) {
                this.changeCustomerConfirmDialog.off();
                this.changeCustomerConfirmDialog.dispose();
                delete this.changeCustomerConfirmDialog;
            }

            CustomerUserRoleComponent.__super__.dispose.call(this);
        },

        /**
         * Show customer confirmation dialog
         *
         * @param {function()} okCallback
         * @param {function()} cancelCallback
         * @private
         */
        _getCustomerConfirmDialog: function(okCallback, cancelCallback) {
            if (!this.changeCustomerConfirmDialog) {
                this.changeCustomerConfirmDialog = this._createChangeCustomerConfirmationDialog();
            }

            this.changeCustomerConfirmDialog
                .off('ok').on('ok', _.bind(okCallback, this))
                .off('cancel').on('cancel', _.bind(cancelCallback, this));

            this.changeCustomerConfirmDialog.open();
        },

        /**
         * Create change customer confirmation dialog
         *
         * @returns {Modal}
         * @private
         */
        _createChangeCustomerConfirmationDialog: function() {
            return new Modal(this.options.dialogOptions);
        },

        /**
         * Update customer user grid
         *
         * @param {String} value
         * @private
         */
        _updateCustomerUserGrid: function(value) {
            if (value) {
                mediator.trigger('datagrid:setParam:' + this.options.datagridName, 'newCustomer', value);
            } else {
                mediator.trigger('datagrid:removeParam:' + this.options.datagridName, 'newCustomer');
            }

            // Add param to know this request is change customer action
            mediator.trigger('datagrid:setParam:' + this.options.datagridName, 'changeCustomerAction', 1);
            mediator.trigger('datagrid:doReset:' + this.options.datagridName);
        }
    });

    return CustomerUserRoleComponent;
});
