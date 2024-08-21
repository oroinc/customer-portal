import $ from 'jquery';
import FilterItemsHintView from 'oroproduct/js/app/views/sidebar-filters/filter-items-hint-view';

export default {
    processDatagridOptions(deferred, options) {
        if ('filters' in options.metadata && options.metadata.filters.length) {
            if (!options.metadata.options.filtersManager) {
                options.metadata.options.filtersManager = {};
            }

            Object.assign(options.metadata.options.filtersManager, {
                outerHintContainer: `[data-hint-container="${options.gridName}"]`
            });

            options.metadata.filters.forEach(filter => {
                filter.outerHintContainer = `[data-hint-container="${options.gridName}"]`;

                if (options.filterEnableValueBadge !== void 0) {
                    filter.filterEnableValueBadge = options.filterEnableValueBadge;
                }

                if (options.allowClearButtonInFilter !== void 0) {
                    filter.allowClearButtonInFilter = options.allowClearButtonInFilter;
                }
            });
        }

        return deferred.resolve();
    },

    init(deferred, options) {
        options.gridPromise.done(grid => {
            const filterItemsHintView = new FilterItemsHintView({
                gridName: grid.name
            });

            const topToolbar = grid.toolbars.top;
            grid.once('filters:beforeRender', () => {
                if (topToolbar && !topToolbar.disposed) {
                    $(topToolbar.el).find('[data-role="filter-container"]').append(filterItemsHintView.render().el);
                }
            });

            grid.once('dispose', () => {
                if (filterItemsHintView && !filterItemsHintView.disposed) {
                    filterItemsHintView.dispose();
                }
            });
        });

        return deferred.resolve();
    }
};
