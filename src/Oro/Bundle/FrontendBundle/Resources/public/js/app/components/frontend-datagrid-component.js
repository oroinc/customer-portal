define(function(require, exports, module) {
    'use strict';

    const DataGridComponent = require('orodatagrid/js/app/components/datagrid-component');
    const ElasticSwipeActionsPlugin = require('orofrontend/js/app/plugins/plugin-elastic-swipe-actions');
    const _ = require('underscore');

    let config = require('module-config').default(module.id);

    config = _.extend({
        responsiveGridClassName: 'frontend-datagrid--responsive',
        gridHasSwipeClassName: 'frontend-datagrid--has-swipe'
    }, config);

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

        /**
         * @inheritdoc
         */
        initDataGrid: function(options) {
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
            return _.isMobile() &&
                (this.metadata.responsiveGrids && this.metadata.responsiveGrids.enable) &&
                (this.metadata.swipeActionsGrid && this.metadata.swipeActionsGrid.enable);
        }
    });
    return FrontendDataGridComponent;
});
