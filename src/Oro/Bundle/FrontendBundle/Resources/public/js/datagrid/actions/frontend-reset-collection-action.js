define(function(require) {
    'use strict';

    const ResetCollectionAction = require('oro/datagrid/action/reset-collection-action');

    const FrontendResetCollectionAction = ResetCollectionAction.extend({
        optionNames: ResetCollectionAction.prototype.optionNames.concat(['hiddenIfIsNotResettable']),

        /**
         * @property {boolean}
         */
        hiddenIfIsNotResettable: false,

        listen() {
            const listeners = {};

            if (this.hiddenIfIsNotResettable) {
                listeners[`updateState collection`] = 'onUpdateState';
            }

            return listeners;
        },

        constructor: function FrontendResetCollectionAction(...args) {
            FrontendResetCollectionAction.__super__.constructor.apply(this, args);
        },

        createLauncher(options) {
            const result = FrontendResetCollectionAction.__super__.createLauncher.call(this, options);

            if (this.hiddenIfIsNotResettable) {
                this.onUpdateState(this.collection);
            }

            return result;
        },

        onUpdateState(collection) {
            this.launcherInstance.toggleVisibility(!this.isResettable(collection.initialState, collection.state));
        },

        isResettable(previousState, currentState) {
            return this.datagrid.stateIsResettable(previousState, currentState);
        }
    });

    return FrontendResetCollectionAction;
});

