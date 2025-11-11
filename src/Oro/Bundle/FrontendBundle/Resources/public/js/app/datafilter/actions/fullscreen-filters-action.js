define(function(require, exports, module) {
    'use strict';

    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');
    const ToggleFiltersAction = require('orofilter/js/actions/toggle-filters-action').default;
    const FiltersManager = require('orofilter/js/filters-manager').default;
    const CounterBadgeView = require('orofrontend/js/app/views/counter-badge-view');
    const filterSettings = require('oro/filter-settings').default;
    let config = require('module-config').default(module.id);

    config = _.extend({
        showCounterBadge: false
    }, config);

    const FrontendFullScreenFiltersAction = ToggleFiltersAction.extend({
        /**
         * @inheritdoc
         */
        constructor: function FrontendFullScreenFiltersAction(options) {
            FrontendFullScreenFiltersAction.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            const opts = options || {};

            if (!opts.datagrid) {
                throw new TypeError('"datagrid" is required');
            }

            if (!opts.fullscreenFilters) {
                throw new TypeError('The "fullscreenFilters" option is required.');
            }

            if (opts.datagrid.themeOptions.fullScreenViewport) {
                filterSettings.fullScreenViewport = opts.datagrid.themeOptions.fullScreenViewport;
            }

            this.fullscreenFilters = opts.fullscreenFilters;
            FrontendFullScreenFiltersAction.__super__.initialize.call(this, opts);

            this.listenTo(opts.datagrid, 'filterManager:connected', () => {
                this.filterManager = opts.datagrid.filterManager;
                this.updateFiltersStateView();
            });
            this.listenTo(this.fullscreenFilters, 'main-popup:closed', this.updateFiltersStateView);
            this.listenTo(mediator, 'viewport:change', this.updateFiltersStateView);

            if (config.showCounterBadge) {
                this.subview('badge', new CounterBadgeView());
            }

            this.listenTo(mediator, {
                [`filterManager:selectedFilters:count:${this.datagrid.name}`]: this.onUpdateFiltersCount
            });
        },

        updateFiltersStateView() {
            if (
                this.filterManager === void 0 ||
                this.fullscreenFilters.isPopupOpen()
            ) {
                return;
            }

            const mode = this.filterManager.getViewMode();

            if (mode === FiltersManager.MANAGE_VIEW_MODE &&
                (filterSettings.isFullScreen() || this.launcherInstance.isInDialogWidget())
            ) {
                this._initialViewMode = this.filterManager.getViewMode();
                this.filterManager.setViewMode(FiltersManager.STATE_VIEW_MODE);
            } else if (this._initialViewMode &&
                !filterSettings.isFullScreen() && !this.launcherInstance.isInDialogWidget()
            ) {
                this.filterManager.setViewMode(this._initialViewMode);
                delete this._initialViewMode;
            }
        },

        /**
         * @inheritdoc
         */
        execute: function() {
            if (filterSettings.isFullScreen() || this.launcherInstance.isInDialogWidget()) {
                this.showAsFullScreen();
            } else {
                FrontendFullScreenFiltersAction.__super__.execute.call(this);
            }
        },

        /**
         * @inheritdoc
         */
        toggleFilters: function(mode) {
            if (filterSettings.isFullScreen()) {
                FrontendFullScreenFiltersAction.__super__.toggleFilters.call(this, FiltersManager.STATE_VIEW_MODE);
            } else {
                FrontendFullScreenFiltersAction.__super__.toggleFilters.call(this, mode);
            }
        },

        showAsFullScreen() {
            this.fullscreenFilters.showMainPopup();
        },

        /**
         * Handler on filters selected state are changed
         * @param count
         */
        onUpdateFiltersCount: function(count) {
            this.markAsFiltersAsChanged( count > 0);
            this.setBadgeCount(count);
        },

        /**
         * @param {boolean} selected
         */
        markAsFiltersAsChanged(selected = false) {
            if (this.launcherInstance) {
                this.launcherInstance.$el.toggleClass('filters-selected', selected);
            }
        },

        /**
         * Set e new count to badge view if it exists
         * @param count
         */
        setBadgeCount(count) {
            if (this.subviewsByName['badge'] === void 0 || this.subviewsByName['badge'].disposed) {
                return;
            }

            if (typeof count !== 'number') {
                return;
            }

            this.subview('badge').setCount(count);
        },

        createLauncher: function(options) {
            const launcher = FrontendFullScreenFiltersAction.__super__.createLauncher.call(this, options);

            if (config.showCounterBadge) {
                this.launcherInstance.on('render', () => {
                    this.launcherInstance.$el.prepend(this.counterBadgeView.$el);
                });
            }
            return launcher;
        },

        dispose() {
            if (this.disposed) {
                return;
            }

            delete this.filterManager;
            delete this.fullscreenFilters;

            FrontendFullScreenFiltersAction.__super__.dispose.call(this);
        }
    });

    return FrontendFullScreenFiltersAction;
});
