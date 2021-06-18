define(function(require) {
    'use strict';

    const _ = require('underscore');
    const MultiSelectFilter = require('oro/filter/multiselect-filter');
    const FilterCountHelper = require('orofrontend/js/app/filter-count-helper');

    const FrontendMultiSelectFilter = MultiSelectFilter.extend(_.extend({}, FilterCountHelper, {
        /**
         * @inheritdoc
         */
        populateDefault: false,

        /**
         * @property {Object}
         */
        listen: {
            'metadata-loaded': 'onMetadataLoaded',
            'filters-manager:after-applying-state mediator': 'rerenderFilter'
        },

        /**
         * @inheritdoc
         */
        constructor: function FrontendMultiSelectFilter(options) {
            FrontendMultiSelectFilter.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        getTemplateData: function() {
            const templateData = FrontendMultiSelectFilter.__super__.getTemplateData.call(this);

            return this.filterTemplateData(templateData);
        }
    }));

    return FrontendMultiSelectFilter;
});
