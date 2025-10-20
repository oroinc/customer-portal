define(function(require) {
    'use strict';

    const MultiSelectFilter = require('oro/filter/multiselect-filter');
    const MultiValueFilterHintView = require('./multi-value-filter-hint-view').default;

    const FrontendMultiSelectFilter = MultiSelectFilter.extend({
        /**
         * @inheritdoc
         */
        populateDefault: false,

        HintView: MultiValueFilterHintView,

        /**
         * @inheritdoc
         */
        constructor: function FrontendMultiSelectFilter(options) {
            FrontendMultiSelectFilter.__super__.constructor.call(this, options);
        }
    });

    return FrontendMultiSelectFilter;
});
