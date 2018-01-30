define(function(require) {
    'use strict';

    var FrontendSelectFilter;
    var _ = require('underscore');
    var SelectFilter = require('oro/filter/select-filter');
    var MultiselectDecorator = require('orofrontend/js/app/datafilter/frontend-multiselect-decorator');
    var module = require('module');
    var config = module.config();

    config = _.extend({
        closeAfterChose: !_.isMobile(),
        toggleMode: _.isMobile()
    }, config);

    FrontendSelectFilter = SelectFilter.extend({
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
    });

    return FrontendSelectFilter;
});
