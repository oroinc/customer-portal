define(function(require) {
    'use strict';

    /**
     * This helper use in the context of component View
     */
    const _ = require('underscore');
    const $ = require('jquery');

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

            let options = $.extend(true, {}, data.options || {});
            const that = this;
            _.each(options, function(option) {
                option.count = that.counts[option.value] || 0;
                if (option.count === 0 &&
                    !_.contains(that.disabledOptions, option.value) &&
                    !_.contains(data.selected.value, option.value)) {
                    options = _.without(options, option);
                }
            });

            this.visible = !(_.isEmpty(options) && _.isEmpty(this.disabledOptions));
            data.options = options;

            return data;
        }
    };
});
