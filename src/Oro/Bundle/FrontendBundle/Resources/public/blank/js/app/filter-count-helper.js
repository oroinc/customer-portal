define(function(require) {
    'use strict';

    /**
     * This helper use in the context of component View
     */
    var _ = require('underscore');
    var $ = require('jquery');

    return {
        /**
         * @property {Object}
         */
        counts: null,

        /**
         * @property {Object}
         */
        disabledOptions: null,

        /**
         * @param {Object} metadata
         */
        onMetadataLoaded: function(metadata) {
            this.counts = metadata.counts || null;
            this.disabledOptions = metadata.disabledOptions || null;
            this.rerenderFilter();
        },

        rerenderFilter: function() {
            if (this.isRendered()) {
                this.render();
            }
        },

        /**
         * @param {Object} data
         */
        filterTemplateData: function(data) {
            if (this.counts === null) {
                return data;
            }

            var options = $.extend(true, {}, data.options || {});
            _.each(options, function(option) {
                option.count = this.counts[option.value] || 0;
                if (option.count === 0) {
                    if (!_.contains(this.disabledOptions, option.value)) {
                        options = _.without(options, option);
                    }
                }
            }, this);

            this.visible = !(_.isEmpty(options) && _.isEmpty(this.disabledOptions));
            data.options = options;

            return data;
        }
    };
});
