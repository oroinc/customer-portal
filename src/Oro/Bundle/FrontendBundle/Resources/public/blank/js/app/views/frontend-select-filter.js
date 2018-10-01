define(function(require) {
    'use strict';

    var FrontendSelectFilter;
    var _ = require('underscore');
    var SelectFilter = require('oro/filter/select-filter');
    var MultiselectDecorator = require('orofrontend/js/app/datafilter/frontend-multiselect-decorator');
    var FilterCountHelper = require('orofrontend/js/app/filter-count-helper');
    var module = require('module');
    var config = module.config();

    config = _.extend({
        closeAfterChose: !_.isMobile(),
        toggleMode: _.isMobile()
    }, config);

    FrontendSelectFilter = SelectFilter.extend(_.extend({}, FilterCountHelper, {
        /**
         * @property
         */
        closeAfterChose: config.closeAfterChose,

        /**
         * @property
         */
        toggleMode: config.toggleMode,

        /**
         * @property
         */
        MultiselectDecorator: MultiselectDecorator,

        /**
         * Select widget options
         *
         * @property
         */
        widgetOptions: {
            multiple: false,
            classes: 'select-filter-widget'
        },

        /**
         * Selector for filter area
         *
         * @property
         */
        containerSelector: '.filter-criteria-selector',

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
        getTemplateData: function() {
            var templateData = FrontendSelectFilter.__super__.getTemplateData.apply(this, arguments);

            return this.filterTemplateData(templateData);
        },

        /**
         * Set container for dropdown
         * @return {jQuery}
         */
        _setDropdownContainer: function() {
            var $container = null;

            if (this.toggleMode) {
                $container = this.$el.find('.filter-criteria');
            } else {
                $container = this.dropdownContainer;
            }

            return $container;
        },

        /**
         * Handle click on criteria selector
         *
         * @param {Event} e
         * @protected
         */
        _onClickFilterArea: function(e) {
            e.stopPropagation();

            if (this.toggleMode) {
                this.toggleFilter();
            } else {
                FrontendSelectFilter.__super__._onClickFilterArea.apply(this, arguments);
            }
        },

        toggleFilter: function() {
            if (!this.selectDropdownOpened) {
                this._setButtonPressed(this.$(this.containerSelector), true);
                this.selectWidget.multiselect('open');
                this.selectDropdownOpened = true;
            } else {
                this._setButtonPressed(this.$(this.containerSelector), false);
                this.selectDropdownOpened = false;
            }
        },

        /**
         * @inheritDoc
         */
        reset: function() {
            FrontendSelectFilter.__super__.reset.apply(this, arguments);

            if (this.toggleMode) {
                this.selectDropdownOpened = true;
                this.toggleFilter();
            }
        }
    }));

    return FrontendSelectFilter;
});
