define(function(require) {
    'use strict';

    var FrontendDataGridComponent;
    var DataGridComponent = require('orodatagrid/js/app/components/datagrid-component');
    var ElasticSwipeActionsPlugin = require('orofrontend/js/app/plugins/plugin-elastic-swipe-actions');
    var _ = require('underscore');

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

        combineGridOptions: function() {
            var options = FrontendDataGridComponent.__super__.combineGridOptions.apply(this, arguments);

            _.extend(options, this.options);

            if (
                (this.metadata.responsiveGrids && this.metadata.responsiveGrids.enable) &&
                (this.metadata.swipeActionsGrid && this.metadata.swipeActionsGrid.enable)
            ) {
                options.plugins.push({
                    constructor: ElasticSwipeActionsPlugin,
                    options: {
                        containerSelector: '.grid-row',
                        sizerSelector: '.action-cell',
                        viewport: this.metadata.swipeActionsGrid.viewport || {}
                    }
                });
            }

            return options;
        }
    });
    return FrontendDataGridComponent;
});
