define(function(require) {
    'use strict';

    var FrontendBooleanFilter;
    var _ = require('underscore');
    var MultiSelectFilter = require('oro/filter/multiselect-filter');
    var FilterCountHelper = require('orofrontend/js/app/filter-count-helper');

    FrontendBooleanFilter = MultiSelectFilter.extend(_.extend({}, FilterCountHelper, {
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
        constructor: function FrontendBooleanFilter() {
            FrontendBooleanFilter.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        getTemplateData: function() {
            var templateData = FrontendBooleanFilter.__super__.getTemplateData.apply(this, arguments);

            return this.filterTemplateData(templateData);
        }
    }));

    return FrontendBooleanFilter;
});
