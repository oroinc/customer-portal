define(function(require, exports, module) {
    'use strict';

    const _ = require('underscore');
    const SelectFilter = require('oro/filter/select-filter').default;
    const FilterBadgeHintView = require('orofrontend/default/js/app/views/filter-badge-hint-view').default;
    const FilterCountHelper = require('orofrontend/js/app/filter-count-helper');
    const tools = require('oroui/js/tools');
    let config = require('module-config').default(module.id);

    config = _.extend({
        hideHeader: false,
        themeName: 'filter-default',
        closeAfterChose: true,
        minimumResultsForSearch: 7
    }, config);

    const FrontendSelectFilter = SelectFilter.extend(_.extend({}, FilterCountHelper, {
        /**
         * @property
         */
        closeAfterChose: config.closeAfterChose,

        /**
         * Select widget options
         *
         * @property
         */
        widgetOptions: {
            multiple: false,
            maxItemsForShowSearchBar: config.minimumResultsForSearch,
            enabledHeader: config.hideHeader,
            cssConfig: {
                strategy: 'override',
                searchResetBtn: 'btn btn--simple-colored clear-search-button'
            }
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

        events() {
            return {
                [`click ${this.clearFilterSelector}`]: '_onClickClearFilter'
            };
        },

        /**
         * @inheritdoc
         */
        constructor: function FrontendSelectFilter(options) {
            this.onChangeFilter = _.debounce(this.onChangeFilter.bind(this));
            FrontendSelectFilter.__super__.constructor.call(this, options);
        },

        initialize(options) {
            if (config.themeName === 'all-at-once') {
                this.widgetOptions.cssConfig = {
                    ...this.widgetOptions.cssConfig,
                    item: 'filters-dropdown__items filters-dropdown__items--pallet',
                    list: 'filters-dropdown',
                    itemCheckboxLabel: 'filters-dropdown__labels',
                    itemCheckbox: 'filters-dropdown__inputs'
                };
            }

            FrontendSelectFilter.__super__.initialize.call(this, options);
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

        _onClickClearFilter() {
            this.reset();
            this.toggleVisibilityClearFilterButton();
        },

        _showCriteria() {
            FrontendSelectFilter.__super__._showCriteria.call(this);
            this.toggleVisibilityClearFilterButton();
        },

        _onValueChanged() {
            FrontendSelectFilter.__super__._onValueChanged.call(this);
            this.toggleVisibilityClearFilterButton();
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

            const clearFilterButton = this.$(this.clearFilterSelector);
            clearFilterButton.toggleClass('hidden', hidden);
        }
    }));

    return FrontendSelectFilter;
});
