define(function(require) {
    'use strict';

    var FrontendFullScreenFiltersAction;
    var _ = require('underscore');
    var mediator = require('oroui/js/mediator');
    var FiltersManager = require('orofilter/js/filters-manager');
    var ToggleFiltersAction = require('orofilter/js/actions/toggle-filters-action');
    var FullscreenPopupView = require('orofrontend/blank/js/app/views/fullscreen-popup-view');
    var module = require('module');
    var config = module.config();

    config = _.extend({
        popupOptions: {}
    }, config);

    FrontendFullScreenFiltersAction =  ToggleFiltersAction.extend({
        popupOptions: {
            popupBadge: true,
            popupIcon: 'fa-filter',
            popupLabel: _.__('oro.filter.datagrid-toolbar.filters'),
            contentElement: null
        },

        lock: false,

        initialize: function(options) {
            this.popupOptions = _.extend(this.popupOptions, options.popupOptions || {}, config.popupOptions);

            FrontendFullScreenFiltersAction.__super__.initialize.apply(this, arguments);

            mediator.on('filterManager:selectedFilters:count:' + this.datagrid.name, this.onUpdateFiltersCount, this);
        },

        execute: function() {
            this.$filters = this.datagrid.filterManager.$el;
            this.popupOptions.contentElement = this.$filters;

            if (!this.lock) {
                this.prepareContent(this.$filters);

                this.lock = true;
            }

            this.fullscreenView = new FullscreenPopupView(this.popupOptions);

            this.fullscreenView.on('show', function() {
                // todo: Show filters <-- change comment
                this.$filters.show();
            }, this);
            this.fullscreenView.on('close', function() {
                // todo: Hide filters <-- change comment
                this.$filters.hide();

                this.fullscreenView.off();
                this.fullscreenView.dispose();
                delete this.fullscreenView;
            }, this);

            this.fullscreenView.show();
            mediator.trigger('filterManager:selectedFilters:calculate:' + this.datagrid.name);
        },

        onFilterManagerModeChange: function(mode) {
            if (this.launcherInstanse) {
                this.launcherInstanse.$el.toggleClass('pressed', mode === FiltersManager.MANAGE_VIEW_MODE);
            }
            mediator.trigger('layout:adjustHeight');
        },

        prepareContent: function($container) {
            //$container.find('.filter-criteria-selector').removeClass('btn oro-drop-opener oro-dropdown-toggle');
            //$container.find('.filter-criteria').removeClass('dropdown-menu');
        },

        onUpdateFiltersCount: function(count) {
            if (this.fullscreenView) {
                if (count) {
                    this.fullscreenView.setPopupTitle(_.__('oro.filter.datagrid-toolbar.filters_count', {count: count}));
                } else {
                    this.fullscreenView.setPopupTitle(this.popupOptions.popupLabel);
                }
            }
        }
    });

    return FrontendFullScreenFiltersAction;
});
