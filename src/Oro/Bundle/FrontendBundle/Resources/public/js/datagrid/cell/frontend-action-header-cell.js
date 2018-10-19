define(function(require) {
    'use strict';

    var FrontendActionHeaderCell;
    var ActionHeaderCell = require('orodatagrid/js/datagrid/header-cell/action-header-cell');

    FrontendActionHeaderCell = ActionHeaderCell.extend({
        render: function() {
            FrontendActionHeaderCell.__super__.render.call(this);

            var panel = this.subview('actionsPanel');
            if (!panel.haveActions()) {
                this.$el.addClass('action-column--disabled');
            }
        }
    });
    return FrontendActionHeaderCell;
});
