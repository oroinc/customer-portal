define(function(require) {
    'use strict';

    const BaseComponent = require('oroui/js/app/components/base/component');
    const DeleteConfirmation = require('oroui/js/delete-confirmation');
    const mediator = require('oroui/js/mediator');
    const routing = require('routing');
    const __ = require('orotranslation/js/translator');
    const _ = require('underscore');
    const $ = require('jquery');

    const DeleteItemComponent = BaseComponent.extend({
        /**
         * @inheritdoc
         */
        constructor: function DeleteItemComponent(options) {
            DeleteItemComponent.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize(options) {
            this.$elem = options._sourceElement;
            this.url = options.url || routing.generate(options.route, options.routeParams || {});
            this.requestMethod = options.requestMethod || 'DELETE';
            this.redirect = options.redirect;
            this.confirmMessage = options.confirmMessage;
            this.successMessage = options.successMessage || __('item_deleted');
            this.successMessageOptions = options.successMessageOptions || {};
            this.okButtonClass = options.okButtonClass;
            this.cancelButtonClass = options.cancelButtonClass;
            this.triggerData = options.triggerData || null;

            if (_.isObject(this.triggerData) && this.triggerData.lineItemId) {
                this.triggerData.lineItemId = parseInt(this.triggerData.lineItemId, 10);
            }

            this.$elem.on('click', this.deleteItem.bind(this));
        },

        deleteItem() {
            if (this.confirmMessage) {
                this.deleteWithConfirmation();
            } else {
                this.deleteWithoutConfirmation();
            }
        },

        deleteWithConfirmation() {
            const options = _.extend(_.pick(this, 'okButtonClass', 'cancelButtonClass'), {
                content: this.confirmMessage
            });
            const confirm = new DeleteConfirmation(options);

            confirm
                .on('ok', this.deleteWithoutConfirmation.bind(this))
                .open();
        },

        deleteWithoutConfirmation(e) {
            const {
                successMessageOptions: messageOptions,
                successMessage,
                redirect,
                triggerData
            } = this;

            $.ajax({
                url: this.url,
                type: this.requestMethod,
                success: () => {
                    if (redirect) {
                        mediator.execute('showFlashMessage', 'success', successMessage, messageOptions);
                        mediator.execute('redirectTo', {url: redirect}, {redirect: true});
                    } else {
                        mediator.execute('showMessage', 'success', successMessage, {flash: true, ...messageOptions});
                        mediator.trigger('frontend:item:delete', triggerData || e);
                    }
                },
                error(jqXHR) {
                    mediator.execute('hideLoading');

                    const errorCode = 'responseJSON' in jqXHR ? jqXHR.responseJSON.code : jqXHR.status;
                    const errors = 'responseJSON' in jqXHR ? jqXHR.responseJSON.errors.errors : [];
                    if (errorCode === 403) {
                        errors.push(__('oro.ui.forbidden_error'));
                    } else {
                        errors.push(__('oro.ui.unexpected_error'));
                    }

                    _.each(errors, value => {
                        mediator.execute('showFlashMessage', 'error', value);
                    });
                }
            });
        }
    });

    return DeleteItemComponent;
});
