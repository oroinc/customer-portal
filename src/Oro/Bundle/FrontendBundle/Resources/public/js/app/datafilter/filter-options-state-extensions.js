import BaseClass from 'oroui/js/base-class';

const FilterOptionsStateExtensions = BaseClass.extend({
    /**
     * @inheritdoc
     */
    constructor: function FilterOptionsStateExtensions(options) {
        FilterOptionsStateExtensions.__super__.constructor.call(this, options);
    },

    /**
     * @param {Object} filterManager
     */
    saveState(filterManager) {
        this._state = {
            filterManager: {
                autoClose: filterManager.autoClose,
                renderMode: filterManager.renderMode,
                filterContainer: filterManager.filterContainer,
                template: filterManager.template,
                outerHintContainer: filterManager.outerHintContainer,
                enableMultiselectWidget: filterManager.enableMultiselectWidget,
                filtersStateElement: filterManager.filtersStateElement
            },
            filters: Object.entries(filterManager.filters).reduce((obj, [name, filter]) => {
                obj[name] = {
                    autoClose: filter.autoClose,
                    animationDuration: filter.animationDuration,
                    outerHintContainer: filter.outerHintContainer,
                    initiallyOpened: filter.initiallyOpened
                };

                if (filter.type === 'datetime') {
                    obj[name]['timePickerOptions'] = {...filter.timePickerOptions || {}};
                }

                return obj;
            }, {})
        };
    },

    /**
     * @param {Object} filterManager
     */
    restoreState(filterManager) {
        if (!this._state) {
            return;
        }

        for (const [key, value] of Object.entries(this._state.filterManager)) {
            filterManager[key] = value;
        }
        for (const [filterName, filterValues] of Object.entries(this._state.filters)) {
            const filter = filterManager.filters[filterName];

            if (!filter) {
                continue;
            }

            for (const [key, value] of Object.entries(filterValues)) {
                filter[key] = value;
            }
        }
    },

    /**
     * @inheritdoc
     */
    dispose() {
        if (this.disposed) {
            return;
        }
        delete this._state;
        return FilterOptionsStateExtensions.__super__.dispose.call(this);
    }
});

export default FilterOptionsStateExtensions;
