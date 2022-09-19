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
        countsWithoutFilters: null,

        /**
         * @property {Number}
         */
        totalRecordsCount: 0,

        /**
         * @property {Boolean}
         */
        isDisableFiltersEnabled: false,

        /**
         * @param {Object} metadata
         */
        onMetadataLoaded: function(metadata) {
            this.counts = metadata.counts || null;
            this.countsWithoutFilters = metadata.countsWithoutFilters || null;
            this.isDisableFiltersEnabled = metadata.isDisableFiltersEnabled || false;
            this.rerenderFilter();
        },

        /**
         * @param {Number} totalRecordsCount
         */
        onTotalRecordsCountUpdate: function(totalRecordsCount) {
            this.totalRecordsCount = totalRecordsCount;
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
            } else if (_.isEmpty(this.counts)) {
                this.counts = Object.create(null);
            }

            let options = $.extend(true, {}, data.options || {});
            const filterOptions = option => {
                if (this.isDisableFiltersEnabled && _.has(this.countsWithoutFilters, option.value)) {
                    option.disabled = true;
                } else {
                    options = _.without(options, option);
                }
            };

            _.each(options, option => {
                option.count = this.counts[option.value] || 0;
                option.disabled = false;
                if (option.count === 0 &&
                    !_.contains(data.selected.value, option.value)
                ) {
                    filterOptions(option);
                }
            });

            const nonZeroOptions = _.filter(options, option => {
                return option.count > 0;
            });
            if (nonZeroOptions.length === 1) {
                _.each(options, option => {
                    if (option.count === this.totalRecordsCount &&
                        !_.contains(data.selected.value, option.value)
                    ) {
                        filterOptions(option);
                    }
                });
            }

            this.visible = !_.isEmpty(options);
            data.options = options;

            return data;
        }
    };
});
