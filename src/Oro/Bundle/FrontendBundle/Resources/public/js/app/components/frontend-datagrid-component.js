define(function(require) {
    'use strict';

    var FrontendDataGridComponent;
    var DataGridComponent = require('orodatagrid/js/app/components/datagrid-component');
    var ElasticSwipeActionsPlugin = require('orofrontend/js/app/plugins/plugin-elastic-swipe-actions');
    var _ = require('underscore');

    var config = require('module').config();

    config = _.extend({
        responsiveGridClassName: 'frontend-datagrid--responsive',
        gridHasSwipeClassName: 'frontend-datagrid--has-swipe'
    }, config);

    FrontendDataGridComponent = DataGridComponent.extend({
        options: {
            rowActionsClass: 'has-actions',
            rowSelectClass: 'has-select-action'
        },

        /**
         * @inheritDoc
         */
        constructor: function FrontendDataGridComponent() {
            FrontendDataGridComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initDataGrid: function(options) {
            FrontendDataGridComponent.__super__.initDataGrid.apply(this, arguments);

            if ((this.metadata.responsiveGrids && this.metadata.responsiveGrids.enable)) {
                this.$componentEl.addClass(config.responsiveGridClassName);
            }

            if (this.toEnableElasticSwipeActionsPlugin()) {
                this.$componentEl.addClass(config.gridHasSwipeClassName);
            }
        },

        /**
         * @inheritDoc
         */
        combineGridOptions: function() {
            var options = FrontendDataGridComponent.__super__.combineGridOptions.apply(this, arguments);

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
