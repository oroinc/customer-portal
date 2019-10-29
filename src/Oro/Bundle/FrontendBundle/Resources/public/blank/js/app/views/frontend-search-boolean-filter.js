define(function(require) {
    'use strict';

    const _ = require('underscore');
    const MultiSelectFilter = require('oro/filter/multiselect-filter');
    const FilterCountHelper = require('orofrontend/js/app/filter-count-helper');

    const FrontendSearchBooleanFilter = MultiSelectFilter.extend(_.extend({}, FilterCountHelper, {
        /**
         * @inheritDoc
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
         * @inheritDoc
         */
        constructor: function FrontendBooleanFilter(options) {
            FrontendSearchBooleanFilter.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        getTemplateData: function() {
            const templateData = FrontendSearchBooleanFilter.__super__.getTemplateData.call(this);

            return this.filterTemplateData(templateData);
        }
    }));

    return FrontendSearchBooleanFilter;
});
