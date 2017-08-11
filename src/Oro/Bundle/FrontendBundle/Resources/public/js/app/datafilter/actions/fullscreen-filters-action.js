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
        filtersManagerPopupOptions: {}
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
            publicActionLabel: _.__('oro_frontend.filters.apply_all')
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

        execute: function() {
            var filterManager = this.datagrid.filterManager;

            this.$filters = filterManager.$el;
            this.filtersPopupOptions.contentElement = this.$filters;
            this.fullscreenView = new FullScreenPopupView(this.filtersPopupOptions);
            this.fullscreenView.on('show', function() {
                this.$filters.show();
            }, this);
            this.fullscreenView.on('close', function() {
                this.$filters.hide();

                this.fullscreenView.off();
                this.fullscreenView.dispose();
                delete this.fullscreenView;

                this.disposeFiltersManagerPopup();
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

            // Remove handler for close filterManager drowpown when clicking on any other element/anywhere else on the page
            $(document).off(filterManager.selectWidget.multiselect('instance')._namespaceID);
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

        onFilterManagerModeChange: function(mode) {
            if (this.launcherInstanse) {
                this.launcherInstanse.$el.toggleClass('pressed', mode === FiltersManager.MANAGE_VIEW_MODE);
            }
            mediator.trigger('layout:adjustHeight');
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
