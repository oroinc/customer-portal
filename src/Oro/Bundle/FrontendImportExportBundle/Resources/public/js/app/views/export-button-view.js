define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const $ = require('jquery');
    const _ = require('underscore');
    const routing = require('routing');
    const messenger = require('oroui/js/messenger');
    const __ = require('orotranslation/js/translator');

    const ExportButtonView = BaseView.extend({
        /**
         * @property {Object}
         */
        options: {
            entity: null,
            routeOptions: {},
            exportTitle: 'Export',
            exportProcessor: null,
            exportJob: null,
            filePrefix: null,
            successMessage: null,
            errorMessage: null
        },

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

            if (!this.options.exportProcessor) {
                return;
            }

            this.routeOptions = {
                options: this.options.routeOptions,
                entity: this.options.entity,
                exportJob: this.options.exportJob
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
            if (!this.options.exportProcessor) {
                throw new TypeError('"exportProcessor" is required');
            }

            const routeOptions = $.extend(true, {}, this.routeOptions);
            routeOptions.options = $.extend(true, {}, routeOptions.options);

            const exportUrl = routing.generate(this.options.exportRoute, $.extend({}, routeOptions, {
                processorAlias: this.options.exportProcessor,
                filePrefix: this.options.filePrefix
            }));

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
