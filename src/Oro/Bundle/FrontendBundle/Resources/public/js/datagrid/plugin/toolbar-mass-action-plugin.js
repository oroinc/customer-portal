define(function(require, exports, module) {
    'use strict';

    const _ = require('underscore');
    const BasePlugin = require('oroui/js/app/plugins/base/plugin');
    const ShowComponentAction = require('orofrontend/js/datagrid/action/toolbar-mass-action');
    const ToolbarMassActionComponent = require('orodatagrid/js/app/components/toolbar-mass-action-component');

    let config = require('module-config').default(module.id);

    config = _.extend({
        wrapperClassName: 'toolbar-mass-actions'
    }, config);

    const ToolbarMassActionPlugin = BasePlugin.extend({
        enable: function() {
            this.listenTo(this.main, 'beforeToolbarInit', this.onBeforeToolbarInit);
            ToolbarMassActionPlugin.__super__.enable.call(this);
        },

        onBeforeToolbarInit: function(toolbarOptions) {
            const options = {
                datagrid: this.main,
                launcherOptions: _.extend(config, {
                    componentConstructor: ToolbarMassActionComponent,
                    collection: toolbarOptions.collection,
                    actions: this.main.massActions
                })
            };

            if (!toolbarOptions.massActionsPanel) {
                toolbarOptions.massActionsPanel = [];
            }

            toolbarOptions.massActionsPanel.push(new ShowComponentAction(options));
        }
    });

    return ToolbarMassActionPlugin;
});
