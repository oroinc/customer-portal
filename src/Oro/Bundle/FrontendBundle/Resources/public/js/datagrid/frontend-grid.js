define(function(require) {
    'use strict';

    var FrontendGrid;
    var Grid = require('orodatagrid/js/datagrid/grid');

    FrontendGrid = Grid.extend({
        gridOptions: null,

        initialize: function() {
            FrontendGrid.__super__.initialize.apply(this, arguments);
            this.collection.on('reset', function() {
                this.body.render();
            }, this);
        },

        _initColumns: function(options) {
            this.gridOptions = options;
            this.updateRowClassNames();

            FrontendGrid.__super__._initColumns.apply(this, arguments);
        },

        updateRowClassNames: function() {
            if (Object.keys(this.rowActions).length > 0) {
                if (this.gridOptions.rowClassName) {
                    this.gridOptions.rowClassName = this.gridOptions.rowClassName + ' ' + this.rowActionsClass;
                } else {
                    this.gridOptions.rowClassName = this.rowClassName + ' ' + this.rowActionsClass;
                }
            }

            if (this.gridOptions.multiSelectRowEnabled) {
                if (this.gridOptions.rowClassName) {
                    this.gridOptions.rowClassName = this.gridOptions.rowClassName + ' ' + this.rowSelectClass;
                } else {
                    this.gridOptions.rowClassName = this.rowClassName + ' ' + this.rowSelectClass;
                }
            }
        }
    });

    return FrontendGrid;
});
