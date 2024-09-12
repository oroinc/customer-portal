define(function(require, exports, module) {
    'use strict';

    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const SelectFilter = require('oro/filter/select-filter');
    const MultiselectDecorator = require('orofrontend/js/app/datafilter/frontend-multiselect-decorator');
    const FilterBadgeHintView = require('orofrontend/default/js/app/views/filter-badge-hint-view').default;
    const FilterCountHelper = require('orofrontend/js/app/filter-count-helper');
    const tools = require('oroui/js/tools');
    let config = require('module-config').default(module.id);

    config = _.extend({
        closeAfterChose: true,
        minimumResultsForSearch: 7
    }, config);

    const FrontendSelectFilter = SelectFilter.extend(_.extend({}, FilterCountHelper, {
        /**
         * @property
         */
        closeAfterChose: config.closeAfterChose,

        /**
         * @property
         */
        minimumResultsForSearch: config.minimumResultsForSearch,

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
         * Selector to criteria popup container
         *
         * @property {String}
         */
        criteriaSelector: '.filter-criteria',

        /**
         * @property {Object}
         */
        listen: {
            'metadata-loaded': 'onMetadataLoaded',
            'total-records-count-updated': 'onTotalRecordsCountUpdate',
            'filters-manager:after-applying-state mediator': 'rerenderFilter',
            'change': 'onChangeFilter'
        },

        /**
         * @inheritdoc
         */
        constructor: function FrontendSelectFilter(options) {
            this.onChangeFilter = _.debounce(this.onChangeFilter.bind(this));
            FrontendSelectFilter.__super__.constructor.call(this, options);
        },

        rendered() {
            if (this.subview('filter:badge-hint')) {
                this.subview('filter:badge-hint').dispose();
            }

            if (this.filterEnableValueBadge) {
                this.subview('filter:badge-hint', new FilterBadgeHintView({
                    filter: this,
                    container: this.$('.filter-criteria-selector')
                }));
            }

            return FrontendSelectFilter.__super__.rendered.call(this);
        },

        /**
         * @inheritdoc
         */
        getTemplateData: function() {
            const templateData = FrontendSelectFilter.__super__.getTemplateData.call(this);

            return this.filterTemplateData(templateData);
        },

        /**
         * @inheritdoc
         * @return {jQuery}
         */
        _appendToContainer: function() {
            return this.isToggleMode() ? this.$el.find('.filter-criteria') : this.dropdownContainer;
        },

        /**
         * @inheritdoc
         */
        _initializeSelectWidget() {
            this.widgetOptions = Object.assign({}, this.widgetOptions, {
                additionalClass: !this.isToggleMode(),
                resetButton: this.allowClearButtonInFilter ? {
                    label: __('oro.filter.clearFilterButton.text'),
                    attr: {
                        'class': 'btn btn--flat filter-clear hidden',
                        'aria-label': __('oro.filter.clearFilterButton.aria_label', {
                            label: `${__('oro.filter.by')} ${this.label}`}
                        )
                    },
                    onClick: this.onClickSelectWidgetResetButton.bind(this)
                } : null
            });
            this.contextSearch = Array.isArray(this.choices) && this.choices.length > this.minimumResultsForSearch;

            return FrontendSelectFilter.__super__._initializeSelectWidget.call(this);
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
                FrontendSelectFilter.__super__._onClickFilterArea.call(this, e);
            }

            this.toggleVisibilityClearFilterButton();
        },

        toggleFilter: function() {
            if (!this.selectDropdownOpened) {
                this._setButtonPressed(this.$(this.criteriaSelector), true);
                if (this.selectWidget) {
                    this.selectWidget.multiselect('open');
                }
                this.trigger('showCriteria', this);
                this.selectDropdownOpened = true;
            } else {
                this._setButtonPressed(this.$(this.criteriaSelector), false);
                this.selectDropdownOpened = false;
                this.trigger('hideCriteria', this);
            }
        },

        /**
         * @inheritdoc
         */
        reset: function() {
            FrontendSelectFilter.__super__.reset.call(this);

            if (this.isToggleMode() && this.autoClose !== false) {
                this.selectDropdownOpened = true;
                this.toggleFilter();
            }
        },

        /**
         * @inheritdoc
         */
        _getSelectWidgetPosition: function() {
            const position = FrontendSelectFilter.__super__._getSelectWidgetPosition.call(this);

            return _.extend({}, position, {
                my: `${_.isRTL() ? 'right' : 'left'} top`
            });
        },

        isToggleMode: function() {
            return this.renderMode === 'toggle-mode';
        },

        onClickSelectWidgetResetButton() {
            this.reset();
            this.toggleVisibilityClearFilterButton();
        },

        onChangeFilter() {
            this.toggleVisibilityClearFilterButton();
        },

        toggleVisibilityClearFilterButton(hidden) {
            if (hidden === void 0) {
                hidden = tools.isEqualsLoosely(this.getValue(), this.emptyValue);
            }

            this.selectWidget && this.selectWidget.toggleVisibilityResetButton(hidden);
        }
    }));

    return FrontendSelectFilter;
});
