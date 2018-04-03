define(function(require) {
    'use strict';

    var FrontendActionCell;
    var ActionCell = require('oro/datagrid/cell/action-cell');
    var _ = require('underscore');
    var module = require('module');

    var config = module.config();

    FrontendActionCell = ActionCell.extend({
        constructor: function FrontendActionCell() {
            return FrontendActionCell.__super__.constructor.apply(this, arguments);
        },

        initialize: function(options) {
            FrontendActionCell.__super__.initialize.apply(this, arguments);

            if (_.isMobile() && config.actionsHideCount) {
                this.actionsHideCount = config.actionsHideCount;
            }
        }
    });

    return FrontendActionCell;
});
