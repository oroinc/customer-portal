import _ from 'underscore';
import BasePlugin from 'oroui/js/app/plugins/base/plugin';
import ShowComponentAction from 'orofrontend/js/datagrid/action/toolbar-mass-action';
import ToolbarMassActionComponent from 'orodatagrid/js/app/components/toolbar-mass-action-component';
import moduleConfig from 'module-config';

const config = {
    wrapperClassName: 'toolbar-mass-actions',
    ...moduleConfig(module.id)
};

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

export default ToolbarMassActionPlugin;
