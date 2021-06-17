define(function(require, exports, module) {
    'use strict';

    const _ = require('underscore');
    const $ = require('jquery');
    const mediator = require('oroui/js/mediator');
    const ToggleFiltersAction = require('orofilter/js/actions/toggle-filters-action');
    const FiltersManager = require('orofilter/js/filters-manager');
    const FullScreenPopupView = require('orofrontend/blank/js/app/views/fullscreen-popup-view');
    const CounterBadgeView = require('orofrontend/js/app/views/counter-badge-view');
    let config = require('module-config').default(module.id);

    config = _.extend({
        filtersPopupOptions: {},
        filtersManagerPopupOptions: {},
        autoClose: false,
        showCounterBadge: false,
        animationDuration: 300
    }, config);

    const FrontendFullScreenFiltersAction = ToggleFiltersAction.extend({
        /**
         * @property;
         */
        filtersPopupOptions: {
            popupBadge: true,
            popupIcon: 'fa-filter',
            popupLabel: _.__('oro.filter.datagrid-toolbar.filters'),
            contentElement: null,
            footerOptions: {
                templateData: {
                    buttons: [
                        {
                            'type': 'button',
                            'class': 'btn btn--info btn--block btn--size-s',
                            'role': 'action',
                            'label': _.__('oro_frontend.filters.apply_all')
                        }
                    ]
                }
            }
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
         * @property;
         */
        isLocked: false,

        /**
         * @property;
         */
        applyAllFiltersSelector: '[data-role="action"]',

        /**
         * @property;
         */
        applyAllFiltersBtn: null,

        /**
         * @property;
         */
        counterBadgeView: CounterBadgeView,

        /**
         * @inheritDoc
         */
        constructor: function FrontendFullScreenFiltersAction(options) {
            FrontendFullScreenFiltersAction.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
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

            FrontendFullScreenFiltersAction.__super__.initialize.call(this, options);

            if (config.showCounterBadge) {
                this.counterBadgeView = new this.counterBadgeView();
            }

            mediator.on('filterManager:selectedFilters:count:' + this.datagrid.name, this.onUpdateFiltersCount, this);
            mediator.on('filterManager:changedFilters:count:' + this.datagrid.name, this.onChangeFiltersCount, this);
            mediator.on('datagrid:doRefresh:' + this.datagrid.name, this._toggleApplyAllBtn, this);
        },

        /**
         * {@inheritdoc}
         */
        toggleFilters: function(mode) {
            const filterManager = this.datagrid.filterManager;

            if (!filterManager || filterManager.$el.is(':visible') === (mode === FiltersManager.MANAGE_VIEW_MODE)) {
                return;
            }

            if (mode === FiltersManager.STATE_VIEW_MODE && this.fullscreenView) {
                this.fullscreenView.close();

                return;
            }

            this.$filters = filterManager.$el;
            this.filtersPopupOptions.contentElement = this.$filters;
            this.fullscreenView = new FullScreenPopupView(this.filtersPopupOptions);

            this.fullscreenView.on('show', function() {
                const enteredState = this.getChangedFiltersState(this.datagrid);

                this.applyAllFiltersBtn = this.fullscreenView.footer.$el.find(this.applyAllFiltersSelector);

                this.applyAllFiltersBtn.on('click', () => {
                    const state = this.getChangedFiltersState(this.datagrid);

                    if (state.errorsCount === 0) {
                        _.extend(filterManager.collection.state.filters, state.filters);
                        filterManager.collection.trigger('updateState', filterManager.collection);
                        mediator.trigger('datagrid:doRefresh:' + filterManager.collection.inputName);

                        this.fullscreenView.close();
                    }
                });

                this._toggleApplyAllBtn(!_.keys(enteredState.filters).length);

                this.setMessengerContainer();

                this.closeEmptyFilters();

                this.datagrid.filterManager.autoClose = config.autoClose;
                if (config.autoClose === false) {
                    Object.values(this.datagrid.filterManager.filters)
                        .forEach(filter => filter.autoClose = config.autoClose);
                }

                if (config.animationDuration !== void 0) {
                    Object.values(this.datagrid.filterManager.filters)
                        .forEach(filter => filter.animationDuration = config.animationDuration);
                }
            }, this);

            this.fullscreenView.on('close', function() {
                this.removeMessengerContainer();

                this.$filters.hide();

                this.applyAllFiltersBtn.off();
                this.fullscreenView.off();
                this.fullscreenView.dispose();
                delete this.fullscreenView;

                this.disposeFiltersManagerPopup();
                this.datagrid.filterManager.setViewMode(FiltersManager.STATE_VIEW_MODE);
            }, this);

            this.fullscreenView.show();
            this.$filters.show();

            filterManager._publishCountSelectedFilters();

            this.initFiltersManagerPopup(filterManager);

            this.unbindFiltersEvents(filterManager);
        },

        setMessengerContainer: function() {
            this.$filters.prepend(
                $('<div></div>').attr('data-role', 'messenger-temporary-container')
            );
        },

        removeMessengerContainer: function() {
            this.$filters.find('[data-role=messenger-temporary-container]').remove();
        },

        /**
         * @param {object} filterManager
         */
        unbindFiltersEvents: function(filterManager) {
            const self = this;

            if (!_.isObject(filterManager) || this.isLocked) {
                return;
            }

            this.isLocked = true;

            _.each(filterManager.filters, function(filter) {
                if (_.isFunction(filter._eventNamespace)) {
                    $('body').off('click' + filter._eventNamespace());
                }

                if (_.isObject(filter.subviewsByName.hint)) {
                    filter.subviewsByName.hint.on('reset', function() {
                        self._toggleApplyAllBtn(!this.$el.siblings('span').filter(':visible').length);
                    });
                }
            });
        },

        /**
         * @param {object} filterManager
         */
        initFiltersManagerPopup: function(filterManager) {
            if (!_.isObject(filterManager)) {
                return;
            }

            const selectWidget = filterManager.selectWidget;

            if (!_.isObject(selectWidget)) {
                return;
            }
            const $popupMenu = selectWidget.multiselect('getMenu');
            const $popupContent = filterManager.$el;

            this.$filterManagerButton = selectWidget.multiselect('getButton');
            this.$filterManagerButtonContent = this.$filterManagerButton.find('span');
            this.filtersManagerPopupOptions.contentElement = $popupContent;
            this.filterManagerPopup = new FullScreenPopupView(
                this.filtersManagerPopupOptions
            );
            this.filterManagerPopup.on('show', function() {
                $popupContent.find('[data-filters-items]').hide();
                $popupMenu
                    .removeAttr('style')
                    .removeClass('dropdown-menu')
                    .addClass(this.filterManagerClasses)
                    .show();
            }, this);
            this.filterManagerPopup.on('close', function() {
                this.$filterManagerButton.removeClass('pressed');
                $popupContent.find('[data-filters-items]').show();
                $popupMenu
                    .addClass('dropdown-menu')
                    .removeClass(this.filterManagerClasses)
                    .hide();
            }, this);

            const handler = () => {
                this.filterManagerPopup.show();
            };

            // Don't close filter before open Filter Manager
            selectWidget.multiselect('instance').options.beforeopen = function() {
                selectWidget.onBeforeOpenDropdown();
            };

            this.$filterManagerButton.on('click.multiselectfullscreen', handler);
            this.$filterManagerButtonContent.on('click.multiselectfullscreen', handler);
        },

        disposeFiltersManagerPopup: function() {
            if (!_.isUndefined(this.filterManagerPopup) && _.isObject(this.filterManagerPopup)) {
                this.filterManagerPopup.off();
                this.filterManagerPopup.dispose();
                delete this.filterManagerPopup;
            }

            if ((this.$filterManagerButton instanceof $) && (this.$filterManagerButtonContent instanceof $)) {
                this.$filterManagerButton.off('.multiselectfullscreen');
                this.$filterManagerButtonContent.off('.multiselectfullscreen');
                delete this.$filterManagerButton;
                delete this.$filterManagerButtonContent;
            }
        },

        /**
         *
         * @param {object} datagrid
         * @returns {Object}
         */
        getChangedFiltersState: function(datagrid) {
            const state = {
                filters: {},
                errorsCount: 0
            };

            if (!_.isObject(datagrid)) {
                return state;
            }

            const filterManager = datagrid.filterManager;
            const changedFilters = _.clone(filterManager.getChangedFilters());

            if (!changedFilters.length) {
                return state;
            }

            _.each(changedFilters, function(filter) {
                const isValid = _.isFunction(filter._isValid) ? filter._isValid() : true;

                if (isValid) {
                    state.filters[filter.name] = filter._formatRawValue(filter._readDOMValue());
                } else {
                    state.errorsCount += 1;
                }
            });

            return state;
        },

        closeEmptyFilters: function() {
            const filters = this.datagrid.filterManager.filters;

            _.each(filters, function(filter) {
                if (
                    filter.enabled && (filter.type === 'multichoice'
                        ? filter._readDOMValue().value.length === 0
                        : _.isEqual(filter.emptyValue, filter._readDOMValue()))
                ) {
                    if (!_.isFunction(filter._onClickCriteriaSelector)) {
                        return;
                    }
                    if (_.has(filter, 'selectDropdownOpened')) {
                        filter.selectDropdownOpened = true;
                    }

                    if (_.has(filter, 'popupCriteriaShowed')) {
                        filter.popupCriteriaShowed = true;
                    }

                    filter._onClickCriteriaSelector($.Event('click'));
                }
            });
        },

        onUpdateFiltersCount: function(count) {
            if (this.fullscreenView) {
                if (_.isNumber(count) && count > 0) {
                    this.fullscreenView.setPopupTitle(
                        _.__('oro.filter.datagrid-toolbar.filters_count', {count: count})
                    );
                    this._toggleApplyAllBtn(!count);
                } else {
                    this.fullscreenView.setPopupTitle(this.filtersPopupOptions.popupLabel);
                }
            }

            if (config.showCounterBadge && _.isNumber(count)) {
                this.counterBadgeView.setCount(count);
            }
        },

        onChangeFiltersCount: function(count) {
            this._toggleApplyAllBtn(!count);
        },

        _toggleApplyAllBtn: function(state) {
            const disable = _.isUndefined(state) ? true : state;
            if (this.applyAllFiltersBtn && this.applyAllFiltersBtn.length) {
                this.applyAllFiltersBtn.attr({
                    disabled: disable
                });
            }
        },

        createLauncher: function(options) {
            const launcher = FrontendFullScreenFiltersAction.__super__.createLauncher.call(this, options);

            if (config.showCounterBadge) {
                const self = this;

                this.launcherInstance.on('render', function() {
                    this.$el.prepend(self.counterBadgeView.$el);
                });
            }

            return launcher;
        }
    });

    return FrontendFullScreenFiltersAction;
});
