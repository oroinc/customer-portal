import AbstractAction from 'oro/datagrid/action/abstract-action';
import toolbarMassActionLauncher from 'orofrontend/js/datagrid/toolbar-mass-action-launcher';

const ToolbarMassAction = AbstractAction.extend({
    launcher: toolbarMassActionLauncher,

    order: 50,

    /**
     * @inheritdoc
     */
    constructor: function ToolbarMassAction(options) {
        ToolbarMassAction.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    execute: function() {
        // do nothing
    }
});

export default ToolbarMassAction;
