define(function(require) {
    'use strict';

    var FrontendColumnManagerComponent;
    var ColumnManagerComponent = require('orodatagrid/js/app/components/column-manager-component');
    var FrontendColumnManagerView = require('orofrontend/js/app/views/column-manager/frontend-column-manager-view');

    /**
     * @class FrontendColumnManagerComponent
     * @extends ColumnManagerComponent
     */
    FrontendColumnManagerComponent = ColumnManagerComponent.extend({
        columnManagerView: FrontendColumnManagerView,
        /**
         * Check if filters enabled
         * @type {boolean}
         */
        enableFilters: false,

        /**
         * @inheritDoc
         */
        constructor: function FrontendColumnManagerComponent(options) {
            FrontendColumnManagerComponent.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        beforeOpen: function(showEvent) {
            FrontendColumnManagerComponent.__super__.beforeOpen.call(this, showEvent);
            this.columnManagerView.beforeOpen(showEvent);
        }
    });

    return FrontendColumnManagerComponent;
});
