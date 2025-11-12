import DataGridComponent from 'orodatagrid/js/app/components/datagrid-component';
import ElasticSwipeActionsPlugin from 'orofrontend/js/app/plugins/plugin-elastic-swipe-actions';
import _ from 'underscore';
import moduleConfig from 'module-config';

const config = {
    responsiveGridClassName: 'frontend-datagrid--responsive',
    gridHasSwipeClassName: 'frontend-datagrid--has-swipe',
    ...moduleConfig(module.id),
    themeOptions: {
        enabledAccessibilityPlugin: true,
        ...moduleConfig(module.id).themeOptions
    }
};

const FrontendDataGridComponent = DataGridComponent.extend({
    options: {
        rowActionsClass: 'has-actions',
        rowSelectClass: 'has-select-action'
    },

    /**
     * @inheritdoc
     */
    constructor: function FrontendDataGridComponent(options) {
        FrontendDataGridComponent.__super__.constructor.call(this, options);
    },

    initialize(options) {
        if (options.enableFilters && 'filters' in options.metadata && options.metadata.filters.length) {
            options.builders.push('orofrontend/js/datagrid/builder/frontend-filters-builder');
        }

        FrontendDataGridComponent.__super__.initialize.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initDataGrid: function(options) {
        options = {
            ...options,
            themeOptions: {
                ...config.themeOptions,
                ...options.themeOptions
            }
        };

        FrontendDataGridComponent.__super__.initDataGrid.call(this, options);

        if ((this.metadata.responsiveGrids && this.metadata.responsiveGrids.enable)) {
            this.$componentEl.addClass(config.responsiveGridClassName);
        }

        if (this.toEnableElasticSwipeActionsPlugin()) {
            this.$componentEl.addClass(config.gridHasSwipeClassName);
        }
    },

    /**
     * @inheritdoc
     */
    combineGridOptions: function() {
        const options = FrontendDataGridComponent.__super__.combineGridOptions.call(this);

        _.extend(options, this.options);

        if (this.toEnableElasticSwipeActionsPlugin()) {
            options.plugins.push(ElasticSwipeActionsPlugin);
        }

        return options;
    },

    /**
     * @returns {boolean}
     */
    toEnableElasticSwipeActionsPlugin: function() {
        return _.isTouchDevice() &&
            this.metadata.responsiveGrids?.enable &&
            this.metadata.swipeActionsGrid?.enable;
    }
});
export default FrontendDataGridComponent;
