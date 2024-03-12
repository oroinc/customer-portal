define(function(require, exports, module) {
    'use strict';

    const ActionCell = require('oro/datagrid/cell/action-cell');
    const _ = require('underscore');
    const config = require('module-config').default(module.id);

    const FrontendActionCell = ActionCell.extend({
        constructor: function FrontendActionCell(options) {
            return FrontendActionCell.__super__.constructor.call(this, options);
        },

        initialize: function(options) {
            FrontendActionCell.__super__.initialize.call(this, options);

            if (_.isMobile() && config.actionsHideCount) {
                this.actionsHideCount = config.actionsHideCount;
            }
        },

        /**
         * Add extra classes to launcher
         *
         * @param {orodatagrid.datagrid.ActionLauncher} launcher
         * @param {Object=} params
         * @return {jQuery} Rendered element wrapped with jQuery
         */
        decorateLauncherItem(launcher) {
            FrontendActionCell.__super__.decorateLauncherItem.call(this, launcher);

            if (!launcher.$el) {
                return this;
            }

            const extraClass = config?.extraClass[launcher.launcherMode];

            if (extraClass) {
                launcher.$el.addClass(extraClass);
            }

            return this;
        }
    });

    return FrontendActionCell;
});
