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
            this.removeClass = options.removeClass;
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
            // preserve data for callback methods, due to the instance might be already disposed at that time
            const context = {
                successMessageOptions: this.successMessageOptions,
                successMessage: this.successMessage,
                redirect: this.redirect,
                triggerData: this.triggerData
            };
            $.ajax({
                url: this.url,
                type: this.requestMethod,
                success: () => {
                    if (this.redirect) {
                        this.deleteWithRedirect.call(context, e);
                    } else {
                        this.deleteWithoutRedirect.call(context, e);
                    }

                    if (this.removeClass) {
                        this.$elem.closest('.' + this.removeClass)
                            .trigger('content:remove').remove();
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
        },

        deleteWithRedirect(e) {
            const messageOptions = this.successMessageOptions;
            mediator.execute('showFlashMessage', 'success', this.successMessage, messageOptions);
            mediator.execute('redirectTo', {url: this.redirect}, {redirect: true});
        },

        deleteWithoutRedirect(e) {
            const messageOptions = this.successMessageOptions;
            mediator.execute('showMessage', 'success', this.successMessage, {flash: true, ...messageOptions});
            mediator.trigger('frontend:item:delete', this.triggerData || e);
        }
    });

    return DeleteItemComponent;
});
