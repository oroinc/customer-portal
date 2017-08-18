define(function(require) {
    'use strict';

    var FrontendFullScreenFiltersAction;
    var _ = require('underscore');
    var $ = require('jquery');
    var mediator = require('oroui/js/mediator');
    var FiltersManager = require('orofilter/js/filters-manager');
    var ToggleFiltersAction = require('orofilter/js/actions/toggle-filters-action');
    var FullScreenPopupView = require('orofrontend/blank/js/app/views/fullscreen-popup-view');
    var module = require('module');
    var config = module.config();

    config = _.extend({
        filtersPopupOptions: {},
        filtersManagerPopupOptions: {},
        hidePreviousOpenFilters: false
    }, config);

    FrontendFullScreenFiltersAction =  ToggleFiltersAction.extend({
        /**
         * @property;
         */
        filtersPopupOptions: {
            popupBadge: true,
            popupIcon: 'fa-filter',
            popupLabel: _.__('oro.filter.datagrid-toolbar.filters'),
            contentElement: null,
            showFooter: true,
            actionBtnLabel: _.__('oro_frontend.filters.apply_all')
        },

        /**
         * @property;
         */
        filtersManagerPopupOptions: {
            popupBadge: true,
            popupIcon: 'fa-plus',
            popupLabel: _.__('oro_frontend.filter_manager.title'),
            contentElement: null
        },

        /**
         * @property;
         */
        filterManagerClasses: ' datagrid-manager ui-widget-fullscreen',

        /**
         * {@inheritdoc}
         * @param {object} options
         */
        initialize: function(options) {
            this.filtersPopupOptions = _.extend(
                                           this.filtersPopupOptions,
                                           options.filtersPopupOptions || {},
                                           config.filtersPopupOptions
                                       );
            this.filtersManagerPopupOptions = _.extend(
                                                  this.filtersManagerPopupOptions,
                                                  options.filtersManagerPopupOptions || {},
                                                  config.filtersManagerPopupOptions
                                              );

            FrontendFullScreenFiltersAction.__super__.initialize.apply(this, arguments);

            mediator.on('filterManager:selectedFilters:count:' + this.datagrid.name, this.onUpdateFiltersCount, this);
        },

        /**
         * {@inheritdoc}
         */
        execute: function() {
            var self = this;
            var filterManager = this.datagrid.filterManager;

            this.$filters = filterManager.$el;
            this.filtersPopupOptions.contentElement = this.$filters;
            this.fullscreenView = new FullScreenPopupView(this.filtersPopupOptions);
            this.fullscreenView.extendableCallback = _.wrap(this.fullscreenView.extendableCallback, function(original) {
                self.applyAllFilter(self.datagrid);

                // Call native method;
                return original.call(self.fullscreenView);
            });

            this.fullscreenView.on('show', function() {
                this.openNotEmptyFilters();

                this.$filters.show();
            }, this);

            this.fullscreenView.on('close', function() {
                this.$filters.hide();

                this.fullscreenView.off();
                this.fullscreenView.dispose();
                delete this.fullscreenView;

                this.disposeFiltersManagerPopup();

                // Hide Filters Container
                // STATE_VIEW_MODE = 1
                filterManager.setViewMode(FiltersManager.STATE_VIEW_MODE);
            }, this);

            this.fullscreenView.show();

            filterManager._publishCountSelectedFilters();

            this.initFiltersManagerPopup(filterManager);
        },

        /**
         * @param {object} filterManager
         */
        initFiltersManagerPopup: function(filterManager) {
            if (!_.isObject(filterManager)) {
                return ;
            }

            var $popupContent = filterManager.selectWidget.multiselect('getMenu');

            this.$filterManagerButton = filterManager.selectWidget.multiselect('getButton');
            this.$filterManagerButtonContent = this.$filterManagerButton.find('span');
            this.filtersManagerPopupOptions.contentElement = $popupContent;
            this.filterManagerPopup = new FullScreenPopupView(
                this.filtersManagerPopupOptions
            );
            this.filterManagerPopup.on('show', function() {
                $popupContent
                    .removeAttr('style')
                    .removeClass('dropdown-menu')
                    .addClass(this.filterManagerClasses)
                    .show();
            }, this);
            this.filterManagerPopup.on('close', function() {
                $popupContent
                    .addClass('dropdown-menu')
                    .removeClass(this.filterManagerClasses)
                    .hide();
            }, this);

            var handler = _.bind(function() {
                this.filterManagerPopup.show();
            }, this);

            this.$filterManagerButton.on('click.multiselect', handler);
            this.$filterManagerButtonContent.on('click.multiselect', handler);
        },

        disposeFiltersManagerPopup: function() {
            if (!_.isUndefined(this.filterManagerPopup) && _.isObject(this.filterManagerPopup)) {
                this.filterManagerPopup.off();
                this.filterManagerPopup.dispose();
                delete this.filterManagerPopup;
            }

            if ((this.$filterManagerButton instanceof $) && (this.$filterManagerButtonContent instanceof $)) {
                this.$filterManagerButton.off();
                this.$filterManagerButtonContent.off();
                delete this.$filterManagerButton;
                delete this.$filterManagerButtonContent;
            }
        },

        /**
         * @param {object} datagrid
         */
        applyAllFilter: function(datagrid) {
            if (!_.isObject(datagrid)) {
                return ;
            }

            var filterManager = datagrid.filterManager;
            var filters = {};

            _.each(openFilters, function(filter) {
                if (filter.enabled && !_.isEqual(filter.emptyValue, filter._readDOMValue())) {
                    filters[filter.name] = filter._readDOMValue();
                }
            });

            _.extend(filterManager.collection.state.filters, filters);

            filterManager.collection.trigger('updateState', filterManager.collection);
            mediator.trigger('datagrid:doRefresh:' + filterManager.collection.inputName);
        },

        /**
         * {@inheritdoc}
         */
        onFilterManagerModeChange: function(mode) {
            // Must be empty, override original method
        },

        openNotEmptyFilters: function() {
            var filters = this.datagrid.filterManager.filters;

            this.datagrid.filterManager.hidePreviousOpenFilters = config.hidePreviousOpenFilters;

            _.each(filters, function(filter) {
                if ((filter.enabled && !_.isEqual(filter.emptyValue, filter.value)) &&
                    _.isFunction(filter._showCriteria)) {
                    filter.popupCriteriaShowed = false;
                    filter._showCriteria();
                }
            });
        },

        onUpdateFiltersCount: function(count) {
            if (this.fullscreenView) {
                if (_.isNumber(count) && count > 0) {
                    this.fullscreenView.setPopupTitle(
                        _.__('oro.filter.datagrid-toolbar.filters_count', {count: count})
                    );
                } else {
                    this.fullscreenView.setPopupTitle(this.filtersPopupOptions.popupLabel);
                }
            }
        }
    });

    return FrontendFullScreenFiltersAction;
});
