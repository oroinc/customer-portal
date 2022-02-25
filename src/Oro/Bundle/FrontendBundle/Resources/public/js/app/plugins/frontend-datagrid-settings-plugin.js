define(function(require, exports, module) {
    'use strict';

    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const tools = require('oroui/js/tools');
    const ShowComponentAction = require('oro/datagrid/action/show-component-action');
    const DatagridSettingsPlugin = require('orodatagrid/js/app/plugins/grid/datagrid-settings-plugin');
    const DatagridSettingView = require('orodatagrid/js/app/views/grid/datagrid-settings-view');

    let config = require('module-config').default(module.id);
    config = _.extend({
        icon: 'cog',
        wrapperClassName: 'datagrid-settings',
        label: __('oro.datagrid.settings.title'),
        ariaLabel: __('oro.datagrid.settings.title_aria_label'),
        attributes: {
            'data-placement': (tools.isMobile() ? 'bottom-end' : 'left-start')
        }
    }, config);

    /**
     * @class FrontendDatagridSettingsPlugin
     * @extends DatagridSettingsPlugin
     */
    const FrontendDatagridSettingsPlugin = DatagridSettingsPlugin.extend({
        /**
         * @inheritdoc
         */
        onBeforeToolbarInit: function(toolbarOptions) {
            const options = {
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
