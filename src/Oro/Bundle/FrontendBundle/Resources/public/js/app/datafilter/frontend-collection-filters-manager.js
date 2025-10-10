define(function(require, exports, module) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const CollectionFiltersManager = require('orofilter/js/collection-filters-manager');
    const FrontendCollapsableHintsView = require('./frontend-collapsable-hints-view').default;
    const ScrollShadowView = require('orofrontend/js/app/views/scroll-shadow-view').default;
    const {MultiselectDropdown} = require('oroui/js/app/views/multiselect');

    let config = require('module-config').default(module.id);
    config = _.extend({
        templateData: {
            attributes: ''
        },
        enableMultiselectWidget: true,
        filterManagerMenuParams: {
            enabledFooter: true,
            maxItemsForShowSearchBar: 15,
            showSelectedInLabel: false,
            dropdownMenuLabel: __('oro_frontend.filter_manager.label'),
            dropdownAriaLabel: __('oro_frontend.filter_manager.button_aria_label'),
            checkAllText: __('oro_frontend.filter_manager.checkAll'),
            uncheckAllText: __('oro_frontend.filter_manager.unCheckAll'),
            listAriaLabel: __('oro_frontend.filter_manager.listAriaLabel'),
            resetButtonLabel: __('oro_frontend.filter_manager.resetFilter'),
            dropdownToggleIcon: 'settings',
            dropdownToggleLabel: '',
            dropdownDisablePopper: true,
            dropdownPlacement: 'bottom-end',
            cssConfig: {
                strategy: 'override',
                main: 'datagrid-manager',
                item: 'multiselect__item datagrid-manager__list-item',
                dropdownMenu: 'default datagrid-manager__menu dropdown-menu multiselect__dropdown-menu',
                dropdownToggleBtn: 'btn btn--neutral dropdown-toggle filters-manager-trigger select-filter-widget',
                dropdownMenuLabel: 'datagrid-manager__title'
            }
        }
    }, config);

    const FrontendCollectionFiltersManager = CollectionFiltersManager.extend({
        /**
         * Define view constructor for filter manager menu
         *
         * @property {MultiSelectView}
         */
        FilterManagerMenu: MultiselectDropdown,

        /**
         * @inheritdoc
         */
        enableMultiselectWidget: true,

        enableScrollContainerShadow: false,

        /**
         * Filter manager menu params difinition
         *
         * @property {object}
         */
        filterManagerMenuParams: config.filterManagerMenuParams,

        /**
         * @inheritdoc
         */
        templateData: config.templateData,

        optionNames: CollectionFiltersManager.prototype.optionNames.concat([
            'fullscreenTemplate', 'filtersStateElement', 'filterEnableValueBadge', 'allowClearButtonInFilter',
            'hintsToggledStatus', 'enableScrollContainerShadow',
            'enableMultiselectWidget', 'filterManagerMenuParams', 'FilterManagerMenu'
        ]),

        hintsExpanded: false,

        /**
         * @inheritdoc
         */
        constructor: function FrontendCollectionFiltersManager(options) {
            // If filterManagerMenuParams is provided as an object in options,
            // merge its cssConfig property with the default cssConfig, giving precedence to provided values.
            // Then, merge the entire filterManagerMenuParams object with the default one,
            // ensuring any missing properties are filled from the defaults.

            if (typeof options.filterManagerMenuParams === 'object') {
                if (options.filterManagerMenuParams.cssConfig) {
                    options.filterManagerMenuParams.cssConfig =
                        _.defaults(options.filterManagerMenuParams.cssConfig, this.filterManagerMenuParams.cssConfig);
                }
                options.filterManagerMenuParams =
                    _.defaults(options.filterManagerMenuParams, this.filterManagerMenuParams);
            }

            FrontendCollectionFiltersManager.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        render: function() {
            FrontendCollectionFiltersManager.__super__.render.call(this);

            if (this.filtersStateElement && this.filtersStateElement instanceof $) {
                this.filtersStateElement.remove();
            }

            this.finallyOfRender();
            return this;
        },

        /**
         * @inheritdoc
         */
        getTemplateData: function() {
            let data = FrontendCollectionFiltersManager.__super__.getTemplateData.call(this);
            data = $.extend(data, this.templateData || {});
            return data;
        },

        /**
         * @inheritdoc
         */
        _onCollectionReset: function(collection) {
            if (!_.isMobile()) {
                FrontendCollectionFiltersManager.__super__._onCollectionReset.call(this, collection);
            }
        },

        _onFilterUpdated() {
            FrontendCollectionFiltersManager.__super__._onFilterUpdated.call(this);
            this.subview('collapsableHints') && this.subview('collapsableHints').update();
        },

        _onFilterChanged() {
            FrontendCollectionFiltersManager.__super__._onFilterChanged.call(this);
            this.subview('collapsableHints') && this.subview('collapsableHints').update();
        },

        _onFilterDisabled(filter) {
            FrontendCollectionFiltersManager.__super__._onFilterDisabled.call(this, filter);
            this.subview('collapsableHints') && this.subview('collapsableHints').update();
        },

        getFiltersCollectionAsSelectableList({asDefault = false} = {}) {
            return Object.values(this.filters).map(filter => ({
                value: filter.name,
                label: filter.label,
                selected: asDefault ? filter.renderableByDefault : filter.renderable,
                hidden: !filter.visible
            }));
        },

        _onReset(event) {
            FrontendCollectionFiltersManager.__super__._onReset.call(this, event);

            this.updateFiltersManagerMultiselectState();
        },

        _processFilterStatus(activeFilters) {
            FrontendCollectionFiltersManager.__super__._processFilterStatus.call(this, activeFilters);

            this.updateFiltersManagerMultiselectState();
        },

        checkFiltersVisibility() {
            FrontendCollectionFiltersManager.__super__.checkFiltersVisibility.call(this);

            this.updateFiltersManagerMultiselectState();
        },

        updateFiltersManagerMultiselectState() {
            this.subview('filter-manager-menu') &&
                this.subview('filter-manager-menu').setState(this.getFiltersCollectionAsSelectableList());
        },

        finallyOfRender: function() {
            if (this.$el.data('layout') === 'separate') {
                this.initLayout();
            }

            if (this.enableMultiselectWidget) {
                this.subview('filter-manager-menu', new this.FilterManagerMenu({
                    container: this.$('[data-role="filter-actions"]'),
                    options: this.getFiltersCollectionAsSelectableList(),
                    defaultOptions: this.getFiltersCollectionAsSelectableList({asDefault: true}),
                    autoRender: true,
                    ...this.filterManagerMenuParams
                }));

                this.listenTo(this.subview('filter-manager-menu'), 'change:selected', this._onChangeFilterSelect);
            }

            this.subview('collapsableHints', new FrontendCollapsableHintsView({
                autoRender: true,
                filterManager: this,
                filters: this.filters,
                container: this.getHintContainer(),
                toggled: this.hintsExpanded
            }));

            this.listenTo(
                this.subview('collapsableHints'),
                'hints:change-visibility',
                toggledStatus => this.hintsExpanded = toggledStatus
            );

            if (this.renderMode === 'toggle-mode' && this.enableScrollContainerShadow) {
                this.subview('scroll-shadow', new ScrollShadowView({
                    el: this.el,
                    scrollTarget: '[data-filters-items]'
                }));
            }
        }
    });

    return FrontendCollectionFiltersManager;
});
