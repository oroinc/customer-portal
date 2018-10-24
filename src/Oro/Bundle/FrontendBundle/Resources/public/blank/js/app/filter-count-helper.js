define(function(require) {
    'use strict';

    /**
     * This helper use in the context of component View
     */
    var _ = require('underscore');

    return {
        /**
         * @property {Object}
         */
        counts: null,

        /**
         * @param {Object} metadata
         */
        onMetadataLoaded: function(metadata) {
            this.counts = metadata.counts || null;
            this.rerenderFilter();
        },

        rerenderFilter: function() {
            if (this.counts !== null && this.isRendered()) {
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

            var options = data.options || {};
            options = _.filter(options, function(option) {
                option.count = this.counts[option.value] || 0;
                var normalizedValue = this.value.value || this.value;

                return normalizedValue.indexOf(option.value) >= 0 || option.count > 0;
            }, this);

            this.visible = !_.isEmpty(options);
            data.options = options;

            return data;
        }
    };
});
