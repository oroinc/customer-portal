define(function(require) {
    'use strict';

    var FrontendFullScreenFiltersAction;
    var _ = require('underscore');
    var $ = require('jquery');
    var mediator = require('oroui/js/mediator');
    var ToggleFiltersAction = require('orofilter/js/actions/toggle-filters-action');
    var FullScreenPopupView = require('orofrontend/blank/js/app/views/fullscreen-popup-view');
    var CounterBadgeView = require('orofrontend/js/app/views/counter-badge-view');
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
            footerContent: true
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

            this.counterBadgeView = new this.counterBadgeView();

            mediator.on('filterManager:selectedFilters:count:' + this.datagrid.name, this.onUpdateFiltersCount, this);
            mediator.on('filterManager:changedFilters:count:' + this.datagrid.name, this.onChangeFiltersCount, this);
            mediator.on('datagrid:doRefresh:' + this.datagrid.name, this._toggleApplyAllBtn, this);
        },

        /**
         * {@inheritdoc}
         */
        execute: function() {
            var filterManager = this.datagrid.filterManager;

            if (!filterManager) {
                return;
            }

            this.$filters = filterManager.$el;
            this.filtersPopupOptions.contentElement = this.$filters;
            this.fullscreenView = new FullScreenPopupView(this.filtersPopupOptions);

            this.fullscreenView.on('show', function() {
                var enteredState = this.getChangedFiltersState(this.datagrid);

                this.applyAllFiltersBtn = this.fullscreenView.$popupFooter.find(this.applyAllFiltersSelector);

                this.applyAllFiltersBtn.on('click', _.bind(function() {
                    var state = this.getChangedFiltersState(this.datagrid);

                    if (state.errorsCount === 0) {
                        _.extend(filterManager.collection.state.filters, state.filters);
                        filterManager.collection.trigger('updateState', filterManager.collection);
                        mediator.trigger('datagrid:doRefresh:' + filterManager.collection.inputName);

                        this.fullscreenView.close();
                    }

                }, this));

                this._toggleApplyAllBtn(!_.keys(enteredState.filters).length);

                this.setMessengerContainer();

                this.closeEmptyFilters();

                this.$filters.show();

                this.datagrid.filterManager.hidePreviousOpenFilters = config.hidePreviousOpenFilters;
            }, this);

            this.fullscreenView.on('close', function() {
                this.removeMessengerContainer();

                this.$filters.hide();

                this.applyAllFiltersBtn.off();
                this.fullscreenView.off();
                this.fullscreenView.dispose();
                delete this.fullscreenView;

                this.disposeFiltersManagerPopup();
            }, this);

            this.fullscreenView.show();

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
            var self = this;

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
                return ;
            }

            var selectWidget = filterManager.selectWidget;

            if (!_.isObject(selectWidget)) {
                return ;
            }
            var $popupMenu = selectWidget.multiselect('getMenu');
            var $popupContent = filterManager.$el;

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
                $popupContent.find('[data-filters-items]').show();
                $popupMenu
                    .addClass('dropdown-menu')
                    .removeClass(this.filterManagerClasses)
                    .hide();
            }, this);

            var handler = _.bind(function() {
                this.filterManagerPopup.show();
            }, this);

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
            var state = {
                filters: {},
                errorsCount: 0
            };

            if (!_.isObject(datagrid)) {
                return state;
            }

            var filterManager = datagrid.filterManager;
            var changedFilters = _.clone(filterManager.getChangedFilters());

            if (!changedFilters.length) {
                return state;
            }

            _.each(changedFilters, function(filter) {
                var isValid = _.isFunction(filter._isValid) ? filter._isValid() : true;

                if (isValid) {
                    state.filters[filter.name] = filter._formatRawValue(filter._readDOMValue());
                } else {
                    state.errorsCount += 1;
                }
            });

            return state;
        },

        /**
         * {@inheritdoc}
         */
        onFilterManagerModeChange: function(mode) {
            // Must be empty, nothing to do
        },

        closeEmptyFilters: function() {
            var filters = this.datagrid.filterManager.filters;

            _.each(filters, function(filter) {
                if (filter.enabled && (filter.type === 'multichoice' ?
                        filter._readDOMValue().value.length === 0 :
                        _.isEqual(filter.emptyValue, filter._readDOMValue())
                    )) {

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

            if (_.isNumber(count)) {
                this.counterBadgeView.setCount(count);
            }
        },

        onChangeFiltersCount: function(count) {
            this._toggleApplyAllBtn(!count);
        },

        _toggleApplyAllBtn: function(state) {
            var disable = _.isUndefined(state) ? true : state;
            if (this.applyAllFiltersBtn && this.applyAllFiltersBtn.length) {
                this.applyAllFiltersBtn.attr({
                    disabled: disable
                });
            }
        },

        createLauncher: function(options) {
            var self = this;
            var launcher = FrontendFullScreenFiltersAction.__super__.createLauncher.apply(this, arguments);

            this.launcherInstanse.on('render', function() {
                this.$el.prepend(self.counterBadgeView.$el);
            });

            return launcher;
        }
    });

    return FrontendFullScreenFiltersAction;
});
