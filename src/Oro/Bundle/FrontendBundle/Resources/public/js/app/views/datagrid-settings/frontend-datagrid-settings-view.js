define(function(require) {
    'use strict';

    var FrontendDatagridSettingsView;
    var DatagridSettingsView = require('orodatagrid/js/app/views/grid/datagrid-settings-view');
    var FrontendDatagridSettingsColumnView = require('orofrontend/js/app/views/datagrid-settings/frontend-datagrid-settings-column-view');
    var DatagridManageColumnView = require('orodatagrid/js/app/views/grid/datagrid-manage-column-view');

    /**
     * @class FrontendDatagridSettingsColumnView
     * @extends DatagridSettingsView
     */
    FrontendDatagridSettingsView = DatagridSettingsView.extend({
        /**
         * @inheritDoc
         */
        constructor: function FrontendDatagridSettingsView() {
            FrontendDatagridSettingsView.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        render: function() {
            FrontendDatagridSettingsView.__super__.render.call(this);

            this.subview('manageGrid', new DatagridManageColumnView({
                _sourceElement: this.$el,
                grid: this.options.grid,
                columns: this.options.columns,
                collection: this.options.collection,
                datagridSettingsListView: FrontendDatagridSettingsColumnView,
                enableFilters: false
            }));
        },

        /**
         * @inheritDoc
         */
        beforeOpen: function(showEvent) {
            FrontendDatagridSettingsView.__super__.beforeOpen.call(this, showEvent);
            this.subview('manageGrid').beforeOpen(showEvent);
        }
    });

    return FrontendDatagridSettingsView;
});
