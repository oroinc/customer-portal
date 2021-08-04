define(function(require) {
    'use strict';

    const DatagridSettingsView = require('orodatagrid/js/app/views/grid/datagrid-settings-view');
    const FrontendDatagridSettingsColumnView = require('orofrontend/js/app/views/datagrid-settings/frontend-datagrid-settings-column-view');
    const DatagridManageColumnView = require('orodatagrid/js/app/views/grid/datagrid-manage-column-view');

    /**
     * @class FrontendDatagridSettingsColumnView
     * @extends DatagridSettingsView
     */
    const FrontendDatagridSettingsView = DatagridSettingsView.extend({
        /**
         * @inheritdoc
         */
        constructor: function FrontendDatagridSettingsView(options) {
            FrontendDatagridSettingsView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
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
         * @inheritdoc
         */
        beforeOpen: function(showEvent) {
            FrontendDatagridSettingsView.__super__.beforeOpen.call(this, showEvent);
            this.subview('manageGrid').beforeOpen(showEvent);
        }
    });

    return FrontendDatagridSettingsView;
});
