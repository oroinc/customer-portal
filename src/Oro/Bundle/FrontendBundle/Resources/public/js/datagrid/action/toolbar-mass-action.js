define(function(require) {
    'use strict';

    const AbstractAction = require('oro/datagrid/action/abstract-action');
    const toolbarMassActionLauncher = require('orofrontend/js/datagrid/toolbar-mass-action-launcher');

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

    return ToolbarMassAction;
});
