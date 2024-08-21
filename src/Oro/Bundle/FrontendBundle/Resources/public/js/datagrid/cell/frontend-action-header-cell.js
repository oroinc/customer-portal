define(function(require) {
    'use strict';

    const __ = require('orotranslation/js/translator');
    const ActionHeaderCell = require('orodatagrid/js/datagrid/header-cell/action-header-cell');
    const actionHeaderCellLabel = require('!tpl-loader!orofrontend/templates/datagrid/action-header-cell-label.html');

    const FrontendActionHeaderCell = ActionHeaderCell.extend({
        initialize: function(options) {
            FrontendActionHeaderCell.__super__.initialize.call(this, options);

            if (!this.column.get('label')) {
                this.column.set('label', __('oro_frontend.datagrid.action_column.label'));
            }
        },

        getTemplateData: function() {
            const data = FrontendActionHeaderCell.__super__.getTemplateData.call(this);

            data.label = this.column.get('label');

            return data;
        },

        render: function() {
            this.$el.attr({
                scope: 'col'
            });

            FrontendActionHeaderCell.__super__.render.call(this);

            const panel = this.subview('actionsPanel');

            if (!panel.haveActions()) {
                this.$el.addClass('action-column--disabled');
                this.$el.empty().addClass('text-center');
                this.$el.append(actionHeaderCellLabel(this.getTemplateData()));
            }

            return this;
        }
    });
    return FrontendActionHeaderCell;
});
