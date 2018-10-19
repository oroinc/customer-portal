define(function(require) {
    'use strict';

    var FrontendDatagridSettingsPlugin;
    var _ = require('underscore');
    var tools = require('oroui/js/tools');
    var ShowComponentAction = require('oro/datagrid/action/show-component-action');
    var DatagridSettingsPlugin = require('orodatagrid/js/app/plugins/grid/datagrid-settings-plugin');
    var DatagridSettingView = require('orodatagrid/js/app/views/grid/datagrid-settings-view');

    var config = require('module').config();
    config = _.extend({
        icon: 'cog',
        wrapperClassName: 'datagrid-settings',
        label: _.__('oro.datagrid.settings.title'),
        attributes: {
            'data-placement': (tools.isMobile() ? 'bottom-end': 'left-start')
        }
    }, config);

    /**
     * @class FrontendDatagridSettingsPlugin
     * @extends DatagridSettingsPlugin
     */
    FrontendDatagridSettingsPlugin = DatagridSettingsPlugin.extend({
        /**
         * @inheritDoc
         */
        onBeforeToolbarInit: function(toolbarOptions) {
            var options = {
                datagrid: this.main,
                launcherOptions: _.extend(config, {
                    componentConstructor: toolbarOptions.componentConstructor || DatagridSettingView,
                    columns: this.main.columns,
                    collection: this.main.columns,
                    allowDialog: false
                }, toolbarOptions.datagridSettings),
                order: 600
            };

            toolbarOptions.addToolbarAction(new ShowComponentAction(options));
        }
    });

    return FrontendDatagridSettingsPlugin;
});
