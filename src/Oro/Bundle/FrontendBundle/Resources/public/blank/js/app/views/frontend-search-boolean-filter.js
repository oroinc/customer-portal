define(function(require) {
    'use strict';

    var FrontendSearchBooleanFilter;
    var _ = require('underscore');
    var MultiSelectFilter = require('oro/filter/multiselect-filter');
    var FilterCountHelper = require('orofrontend/js/app/filter-count-helper');

    FrontendSearchBooleanFilter = MultiSelectFilter.extend(_.extend({}, FilterCountHelper, {
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
            FrontendSearchBooleanFilter.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        getTemplateData: function() {
            var templateData = FrontendSearchBooleanFilter.__super__.getTemplateData.apply(this, arguments);

            templateData = this.filterTemplateData(templateData);
            this.visible = (_.size(templateData.options) > 1);

            return templateData;
        }
    }));

    return FrontendSearchBooleanFilter;
});
