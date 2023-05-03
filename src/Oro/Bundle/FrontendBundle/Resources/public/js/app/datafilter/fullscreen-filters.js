import $ from 'jquery';
import {pick} from 'underscore';
import __ from 'orotranslation/js/translator';
import mediator from 'oroui/js/mediator';
import filterSettings from 'oro/filter-settings';
import FullScreenPopupView from 'orofrontend/default/js/app/views/fullscreen-popup-view';
import FilterOptionsStateExtensions from 'orofrontend/js/app/datafilter/filter-options-state-extensions';

import moduleConfig from 'module-config';

const config = {
    mainPopupOptions: {},
    managerPopupOptions: {},
    autoClose: false,
    showCounterBadge: false,
    animationDuration: 30,
    initiallyOpened: false,
    ...moduleConfig(module.id)
};

const FullscreenFilters = FilterOptionsStateExtensions.extend({
    /**
     * @property;
     */
    mainPopupOptions: {
        popupBadge: true,
        popupIcon: 'fa-filter',
        popupLabel: __('oro.filter.datagrid-toolbar.filters'),
        footerOptions: {
            templateData: {
                buttons: [
                    {
                        'type': 'button',
                        'class': 'btn btn--info btn--block btn--size-s',
                        'role': 'apply',
                        'label': __('oro_frontend.filters.apply_all'),
                        'disabled': 'disabled'
                    }
                ]
            }
        }
    },

    /**
     * @property;
     */
    managerPopupOptions: {
        popupBadge: true,
        popupIcon: 'fa-plus',
        popupLabel: __('oro_frontend.filter_manager.title')
    },

    /**
     * @inheritdoc
     */
    constructor: function FullscreenFilters(options) {
        FullscreenFilters.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize(options) {
        if (!options.datagrid) {
            throw new TypeError('The "datagrid" option is required.');
        }

        this.datagrid = options.datagrid;

        this.mainPopupOptions = {
            ...this.mainPopupOptions,
            ...options.mainPopupOptions || {},
            ...config.mainPopupOptions
        };
        this.managerPopupOptions = {
            ...this.managerPopupOptions,
            ...options.managerPopupOptions || {},
            ...config.managerPopupOptions
        };
    },

    onceFilterManagerConnected() {
        this.filterManager = this.datagrid.filterManager;

        const filtersState = this.filterManager.subviewsByName['filters-state'];

        if (filtersState) {
            this.listenTo(filtersState, 'clicked', () => filterSettings.isFullScreen() && this.showMainPopup());
        }
    },

    transformSelectWidget() {
        const selectWidget = this.filterManager.selectWidget;

        if (!selectWidget) {
            return;
        }

        const $content = selectWidget.multiselect('getMenu');
        const $selectWidgetBtn = selectWidget.multiselect('getButton');
        const multiselect = selectWidget.multiselect('instance');

        $content
            .removeClass('dropdown-menu')
            .addClass('datagrid-manager ui-widget-fullscreen');

        const fullscreenSelectWidget = new FullScreenPopupView({
            ...this.managerPopupOptions,
            contentElement: $content
        });

        fullscreenSelectWidget.on('show', () => {
            fullscreenSelectWidget.$popup.on(
                `click${fullscreenSelectWidget.eventNamespace()}`,
                '[data-role="reset-filters"]', e => this.filterManager._onReset(e)
            );
        });
        fullscreenSelectWidget.on('beforeclose', () => {
            fullscreenSelectWidget.$popup.off(fullscreenSelectWidget.eventNamespace());
        });
        // Disable JS positioning
        // https://stackoverflow.com/questions/16047795/disable-js-positioning-of-jquery-ui-dialog
        multiselect.position = $.noop;
        multiselect.options.menuWidth = '100%';
        multiselect.options.minWidth = '100%';
        // Don't close filter before open Filter Manager
        multiselect.options.beforeopen = () => selectWidget.onBeforeOpenDropdown();
        multiselect.element.on('multiselectopened', () => $content.removeAttr('style'));
        selectWidget.multiselect('close');

        $selectWidgetBtn.add($selectWidgetBtn.find('span'))
            .on('click.multiselectfullscreen', () => fullscreenSelectWidget.show());

        fullscreenSelectWidget.on('close', () => selectWidget.multiselect('close'));
        this.fullScreenPopup.subview('fullscreen:select-widget', fullscreenSelectWidget);
    },

    transformFilters() {
        this.saveState(this.filterManager);

        for (const filter of Object.values(this.filterManager.filters)) {
            filter.outerHintContainer = null;
            filter.initiallyOpened = config.initiallyOpened;

            if (config.autoClose === false) {
                filter.autoClose = config.autoClose;
            }
            if (config.animationDuration !== void 0) {
                filter.animationDuration = config.animationDuration;
            }
        }

        this.filterManager.$el.remove();
        this.filterManager.autoClose = config.autoClose;
        this.filterManager.outerHintContainer = null;
        this.filterManager.renderMode = 'toggle-mode';
        this.filterManager.filterContainer = this.fullScreenPopup.content.Element;
        this.filterManager.template = this.filterManager.fullscreenTemplate
            ? this.filterManager.fullscreenTemplate
            : this.filterManager.template;

        this.filterManager.trigger('filters-render-mode-changed', {
            renderMode: this.filterManager.renderMode,
            isAsInitial: false
        });

        this.filterManager.render();
        this.filterManager.$el.addClass('fullscreen');

        const datetimeFilters = pick(this.filterManager.filters, filter => filter.type === 'datetime');

        for (const filter of Object.values(datetimeFilters)) {
            filter.timePickerOptions = {
                ...filter.timePickerOptions || {},
                // Append the time-picker dropdown into a root filter element to make sure that it is visible on fullscreen dialog
                appendTo: filter.$el
            };
        }

        this.restoreFiltersAppearance();
        this.openNotEmptyFilters();

        this.filterManager.show();
    },

    /**
     * Subscribe on filter events
     */
    listenToFiltersEvents() {
        if (!this.filterManager) {
            return;
        }

        this.listenTo(this.filterManager.collection, 'beforeFetch', this.saveUnsavedFilters);
    },

    /**
     * Unsubscribe from filter events
     */
    stopListeningFiltersEvents() {
        if (!this.filterManager) {
            return;
        }

        this.stopListening(this.filterManager.collection, 'beforeFetch');
    },

    /**
     * Collect all changed filters
     * @param {Object} collection
     * @param {Object} fetchOptions
     */
    saveUnsavedFilters(collection, fetchOptions) {
        const changedFilters = this.getChangedFiltersState().filters;

        if (
            // do not merge changed filters after reset action
            Object.keys(collection.state.filters).length === 0 ||
            Object.keys(changedFilters).length === 0
        ) {
            return;
        }

        collection.updateState({
            filters: Object.assign({}, collection.state.filters, changedFilters)
        });
    },

    showMainPopup() {
        if (this.disposed || this.fullScreenPopup) {
            return;
        }

        this.fullScreenPopup = new FullScreenPopupView({
            ...this.mainPopupOptions,
            popupLabel: this.determineMainPopupTitle(
                this.filterManager._calculateSelectedFilters()
            ),
            contentElement: document.createElement('div')
        });

        this.listenToOnce(this.fullScreenPopup, {
            show: this.onShowMainPopup,
            beforeclose: this.onBeforeCloseMainPopup,
            close: this.onCloseMainPopup
        });
        this.listenTo(mediator, {
            [`filterManager:selectedFilters:count:${this.datagrid.collection.inputName}`]: this.onUpdateFiltersCount,
            [`filterManager:changedFilters:count:${this.datagrid.collection.inputName}`]: count => {
                this.toggleMainPopupBtn(count === 0);
            },
            [`datagrid:doRefresh::${this.datagrid.collection.inputName}`]: this.toggleMainPopupBtn
        });

        this.fullScreenPopup.show();
    },

    onShowMainPopup() {
        this.fullScreenPopup.content.$el.prepend($('<div></div>').attr('data-role', 'messenger-temporary-container'));
        this.fullScreenPopup.$popup.on(
            `click${this.fullScreenPopup.eventNamespace()}`,
            '[data-role="apply"]', this.applyState.bind(this)
        );

        this.transformFilters();
        this.listenToFiltersEvents();
        this.transformSelectWidget();
        this.trigger('main-popup:shown');
    },

    onBeforeCloseMainPopup() {
        this.stopListeningFiltersEvents();
        this.filterManager.$el.remove();
        this.restoreState(this.filterManager);
        this.filterManager.render();
        this.restoreFiltersAppearance();
    },

    restoreFiltersAppearance() {
        const changedFilters = this.getChangedFiltersState().filters;

        for (const [name, filter] of Object.entries(this.filterManager.filters)) {
            if (!filter.renderable) {
                continue;
            }

            if (changedFilters[name]) {
                filter._writeDOMValue(changedFilters[name]);
            }

            filter._updateCriteriaHint();
        }

        this.filterManager._resetHintContainer();
    },

    onCloseMainPopup() {
        this.disposeFullScreenPopup();
        this.trigger('main-popup:closed');
        this.filterManager.trigger('filters-render-mode-changed', {
            renderMode: this.filterManager.renderMode,
            isAsInitial: true
        });
    },

    /**
     * @param {Object} e
     */
    applyState(e) {
        const state = this.getChangedFiltersState();

        if (state.errorsCount === 0) {
            this.filterManager.collection.state.filters = $.extend(true, {},
                this.filterManager.collection.state.filters,
                state.filters
            );
            this.filterManager.collection.trigger('updateState', this.filterManager.collection);
            mediator.trigger(`datagrid:doRefresh:${this.filterManager.collection.inputName}`);
            this.fullScreenPopup.close();
        }
    },

    openNotEmptyFilters() {
        for (const filter of Object.values(this.filterManager.filters)) {
            if (!filter.renderable) {
                continue;
            }

            if (!filter.isEmpty()) {
                let openFunction;

                if (typeof filter._onClickCriteriaSelector === 'function') {
                    openFunction = filter._onClickCriteriaSelector;
                } else if (typeof filter._onClickFilterArea === 'function') {
                    openFunction = filter._onClickFilterArea;
                }
                if (openFunction === void 0) {
                    continue;
                }
                if (filter.selectDropdownOpened !== void 0) {
                    filter.selectDropdownOpened = false;
                }
                if (filter.popupCriteriaShowed !== void 0) {
                    filter.popupCriteriaShowed = false;
                }

                openFunction.call(filter, $.Event('click'));
            }
        }
    },

    /**
     * @returns {Object}
     */
    getChangedFiltersState() {
        const state = {
            filters: {},
            errorsCount: 0
        };

        if (this.filterManager === void 0) {
            return state;
        }

        const changedFilters = this.filterManager.getChangedFilters();

        changedFilters.forEach(filter => {
            const isValid = typeof filter._isValid === 'function' ? filter._isValid() : true;

            if (isValid) {
                let value = filter._formatRawValue(filter._readDOMValue());

                if (typeof filter.swapValues === 'function') {
                    value = filter.swapValues(value);
                }
                state.filters[filter.name] = value;
            } else {
                state.errorsCount += 1;
            }
        });

        return state;
    },

    /**
     * Disable/Enable Apply filters button
     * @param {boolean} [toDisable]
     */
    toggleMainPopupBtn(toDisable) {
        if (!this.fullScreenPopup) {
            return;
        }

        if (toDisable === void 0) {
            toDisable = Object.keys(this.getChangedFiltersState().filters).length === 0;
        }

        this.fullScreenPopup.$popup.find('[data-role="apply"]').attr('disabled', toDisable);
    },

    onUpdateFiltersCount(count) {
        if (!this.fullScreenPopup) {
            return;
        }

        this.fullScreenPopup.setPopupTitle(
            this.determineMainPopupTitle(count)
        );
        this.toggleMainPopupBtn(count === 0);
    },

    determineMainPopupTitle(count) {
        let title = this.mainPopupOptions.popupLabel;

        if (typeof count === 'number' && count > 0) {
            title = __('oro.filter.datagrid-toolbar.filters_count', {count: count});
        }

        return title;
    },

    isPopupOpen() {
        return this.fullScreenPopup !== void 0;
    },

    disposeFullScreenPopup() {
        if (this.fullScreenPopup && !this.fullScreenPopup.disposed) {
            this.fullScreenPopup.off(this.fullScreenPopup.eventNamespace());
            this.fullScreenPopup.removeSubview('fullscreen:select-widget');
            this.fullScreenPopup.dispose();

            delete this.fullScreenPopup;
        }
    },

    /**
     * @inheritdoc
     */
    dispose() {
        if (this.disposed) {
            return;
        }

        this.disposeFullScreenPopup();
        delete this.filterManager;
        delete this.datagrid;
        delete this._prevChangedFilter;
        return FullscreenFilters.__super__.dispose.call(this);
    }
});

export default FullscreenFilters;
