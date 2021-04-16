define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const $ = require('jquery');
    const _ = require('underscore');
    const routing = require('routing');
    const messenger = require('oroui/js/messenger');
    const __ = require('orotranslation/js/translator');
    const mediator = require('oroui/js/mediator');

    const ExportButtonView = BaseView.extend({
        /**
         * @property {Object}
         */
        options: {
            routeOptions: {},
            exportTitle: 'Export',
            successMessage: null,
            errorMessage: null
        },

        /** @property {Object} */
        routeOptions: {},

        /**
         * @inheritDoc
         */
        events: {
            click: 'onExportClick'
        },

        /**
         * @inheritDoc
         */
        constructor: function ExportButtonView(options) {
            ExportButtonView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);

            this.routeOptions = {
                options: this.options.routeOptions
            };
        },

        /**
         * @param {jQuery.Event} e
         */
        onExportClick: function(e) {
            e.preventDefault();

            this.handleExport();
        },

        handleExport: function() {
            if (!this.options.exportRoute) {
                throw new TypeError('"exportRoute" option is required');
            }

            const routeOptions = $.extend(true, {}, this.routeOptions);
            routeOptions.options = $.extend(true, {}, routeOptions.options);

            mediator.trigger('import-export:handleExport', routeOptions.options);

            const exportUrl = routing.generate(this.options.exportRoute, routeOptions);

            $.post(
                exportUrl,
                function(data) {
                    let message;
                    let messageType;
                    if (data.hasOwnProperty('success') && data.success) {
                        message = __(this.options.successMessage);
                        messageType = 'success';
                    } else {
                        message = __(this.options.errorMessage);
                        messageType = 'error';
                    }
                    messenger.notificationMessage(messageType, message);

                    if (data.messages) {
                        _.each(data.messages, function(messages, type) {
                            _.each(messages, function(message) {
                                messenger.notificationMessage(type, message);
                            });
                        });
                    }
                }.bind(this),
                'json'
            );
        }
    });

    return ExportButtonView;
});
