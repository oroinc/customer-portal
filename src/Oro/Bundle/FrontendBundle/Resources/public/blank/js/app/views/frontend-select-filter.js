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
        closeAfterChose: true
    }, config);

    FrontendSelectFilter = SelectFilter.extend(_.extend({}, FilterCountHelper, {
        /**
         * @property
         */
        closeAfterChose: config.closeAfterChose,

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
         * @property {Object}
         */
        listen: {
            'metadata-loaded': 'onMetadataLoaded',
            'filters-manager:after-applying-state mediator': 'rerenderFilter'
        },

        /**
         * @inheritDoc
         */
        constructor: function FrontendSelectFilter() {
            FrontendSelectFilter.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        getTemplateData: function() {
            var templateData = FrontendSelectFilter.__super__.getTemplateData.apply(this, arguments);

            return this.filterTemplateData(templateData);
        },

        /**
         * @inheritDoc
         * @return {jQuery}
         */
        _appendToContainer: function() {
            return this.isToggleMode() ? this.$el.find('.filter-criteria') : this.dropdownContainer;
        },

        /**
         * @inheritDoc
         */
        render: function() {
            if (this.isToggleMode()) {
                this.widgetOptions = _.defaults(this.widgetOptions, {
                    hideHeader: true,
                    additionalClass: false
                });
            }
            return FrontendSelectFilter.__super__.render.apply(this, arguments);
        },

        /**
         * Handle click on criteria selector
         *
         * @param {Event} e
         * @protected
         */
        _onClickFilterArea: function(e) {
            e.stopPropagation();

            if (this.isToggleMode()) {
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

            if (this.isToggleMode()) {
                this.selectDropdownOpened = true;
                this.toggleFilter();
            }
        },

        /**
         * @inheritDoc
         */
        _getSelectWidgetPosition: function() {
            var position = FrontendSelectFilter.__super__._getSelectWidgetPosition.call(this);

            return _.extend({}, position, {
                my: 'left top'
            });
        },

        isToggleMode: function() {
            return this.renderMode === 'toggle-mode';
        }
    }));

    return FrontendSelectFilter;
});
