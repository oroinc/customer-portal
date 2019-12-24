define(function(require) {
    'use strict';

    var DeleteItemComponent;
    var BaseComponent = require('oroui/js/app/components/base/component');
    var DeleteConfirmation = require('oroui/js/delete-confirmation');
    var mediator = require('oroui/js/mediator');
    var routing = require('routing');
    var __ = require('orotranslation/js/translator');
    var _ = require('underscore');
    var $ = require('jquery');

    DeleteItemComponent = BaseComponent.extend({
        /**
         * @inheritDoc
         */
        constructor: function DeleteItemComponent() {
            DeleteItemComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.$elem = options._sourceElement;
            this.url = options.url || routing.generate(options.route, options.routeParams || {});
            this.removeClass = options.removeClass;
            this.requestMethod = options.requestMethod || 'DELETE';
            this.redirect = options.redirect;
            this.confirmMessage = options.confirmMessage;
            this.successMessage = options.successMessage || __('item_deleted');
            this.okButtonClass = options.okButtonClass;
            this.cancelButtonClass = options.cancelButtonClass;
            this.triggerData = options.triggerData || null;

            if (_.isObject(this.triggerData) && this.triggerData.lineItemId) {
                this.triggerData.lineItemId = parseInt(this.triggerData.lineItemId, 10);
            }

            this.$elem.on('click', _.bind(this.deleteItem, this));
        },

        deleteItem: function() {
            if (this.confirmMessage) {
                this.deleteWithConfirmation();
            } else {
                this.deleteWithoutConfirmation();
            }
        },

        deleteWithConfirmation: function() {
            var options = _.extend(_.pick(this, 'okButtonClass', 'cancelButtonClass'), {
                content: this.confirmMessage
            });
            var confirm = new DeleteConfirmation(options);

            confirm
                .on('ok', this.deleteWithoutConfirmation.bind(this))
                .open();
        },

        deleteWithoutConfirmation: function(e) {
            var self = this;
            $.ajax({
                url: self.url,
                type: this.requestMethod,
                success: function() {
                    if (self.redirect) {
                        self.deleteWithRedirect(e);
                    } else {
                        self.deleteWithoutRedirect(e);
                    }

                    if (self.removeClass) {
                        self.$elem.closest('.' + self.removeClass)
                            .trigger('content:remove').remove();
                    }
                },
                error: function(jqXHR) {
                    mediator.execute('hideLoading');

                    var errorCode = 'responseJSON' in jqXHR ? jqXHR.responseJSON.code : jqXHR.status;
                    var errors = 'responseJSON' in jqXHR ? jqXHR.responseJSON.errors.errors : [];
                    if (errorCode === 403) {
                        errors.push(__('oro.ui.forbidden_error'));
                    } else {
                        errors.push(__('oro.ui.unexpected_error'));
                    }

                    _.each(errors, function(value) {
                        mediator.execute('showFlashMessage', 'error', value);
                    });
                }
            });
        },

        deleteWithRedirect: function(e) {
            mediator.execute('showFlashMessage', 'success', this.successMessage);
            mediator.execute('redirectTo', {url: this.redirect}, {redirect: true});
        },

        deleteWithoutRedirect: function(e) {
            mediator.execute('showMessage', 'success', this.successMessage, {flash: true});
            mediator.trigger('frontend:item:delete', this.triggerData || e);
        }
    });

    return DeleteItemComponent;
});
