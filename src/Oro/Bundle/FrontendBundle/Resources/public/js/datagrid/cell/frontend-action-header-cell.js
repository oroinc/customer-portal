define(function(require) {
    'use strict';

    const ActionHeaderCell = require('orodatagrid/js/datagrid/header-cell/action-header-cell');

    const FrontendActionHeaderCell = ActionHeaderCell.extend({
        render: function() {
            FrontendActionHeaderCell.__super__.render.call(this);

            this.$el.attr({
                scope: 'col'
            });

            const panel = this.subview('actionsPanel');
            if (!panel.haveActions()) {
                this.$el.addClass('action-column--disabled');
            }
        }
    });
    return FrontendActionHeaderCell;
});
