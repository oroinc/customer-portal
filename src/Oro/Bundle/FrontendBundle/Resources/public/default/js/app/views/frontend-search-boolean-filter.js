define(function(require) {
    'use strict';

    const _ = require('underscore');
    const MultiSelectFilter = require('oro/filter/multiselect-filter');
    const FilterCountHelper = require('orofrontend/js/app/filter-count-helper');

    const FrontendSearchBooleanFilter = MultiSelectFilter.extend(_.extend({}, FilterCountHelper, {
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
        constructor: function FrontendBooleanFilter(options) {
            FrontendSearchBooleanFilter.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        getTemplateData: function() {
            let templateData = FrontendSearchBooleanFilter.__super__.getTemplateData.call(this);

            templateData = this.filterTemplateData(templateData);
            this.visible = (_.size(templateData.options) > 1);

            return templateData;
        }
    }));

    return FrontendSearchBooleanFilter;
});
