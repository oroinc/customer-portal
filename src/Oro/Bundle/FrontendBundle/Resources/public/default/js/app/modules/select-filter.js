define(function(require) {
    'use strict';

    var _ = require('underscore');
    var SelectFilter = require('oro/filter/select-filter');
    var MultiselectDecorator = require('orofrontend/js/app/datafilter/frontend-multiselect-decorator');

    _.extend(SelectFilter.prototype, {
        closeAfterChose: !_.isMobile(),

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
         * Filter events
         *
         * @property
         */
        events: {
            'keydown select': '_preventEnterProcessing',
            'click .filter-select': '_onClickFilterArea',
            'click .disable-filter': '_onClickDisableFilter',
            'change select': '_onSelectChange',
            'click .filter-criteria-selector': '_onClickCriteriaSelector'
        },

        /**
         * Set container for dropdown
         * @return {jQuery}
         */
        _setDropdownContainer: function() {
            var $container = null;

            if (_.isMobile()) {
                $container =  this.$el.find('.filter-criteria');
            } else {
                $container =  this.dropdownContainer;
            }

            return $container;
        },

        /**
         * Handle click on criteria selector
         *
         * @param {Event} e
         * @protected
         */
        _onClickCriteriaSelector: function(e) {
            e.stopPropagation();

            this.toggleFilter();
        },

        toggleFilter: function() {
            if (!this.selectDropdownOpened) {
                this._setButtonPressed(this.$(this.containerSelector), true);
                setTimeout(_.bind(function() {
                    this.selectWidget.multiselect('open');
                }, this), 50);
            } else {
                this._setButtonPressed(this.$(this.containerSelector), false);
            }

            this.selectDropdownOpened = !this.selectDropdownOpened;
        },

        /**
         * @inheritDoc
         */
        reset: function() {
            SelectFilter.__super__.reset.apply(this, arguments);

            if (_.isMobile()) {
                this.selectDropdownOpened = true;
                this.toggleFilter();
            }
        }
    });
});
