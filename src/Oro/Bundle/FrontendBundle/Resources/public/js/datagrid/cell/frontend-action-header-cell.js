define(function(require) {
    'use strict';

    const __ = require('orotranslation/js/translator');
    const ActionHeaderCell = require('orodatagrid/js/datagrid/header-cell/action-header-cell');
    const actionHeaderCellLabel = require('!tpl-loader!orofrontend/templates/datagrid/action-header-cell-label.html');
    const textUtil = require('oroui/js/tools/text-util');
    const util = require('orodatagrid/js/datagrid/util');

    const FrontendActionHeaderCell = ActionHeaderCell.extend({
        constructor: function FrontendActionHeaderCell(...args) {
            FrontendActionHeaderCell.__super__.constructor.apply(this, args);
        },

        themeOptions: {
            optionPrefix: 'actionHeaderCell'
        },

        initialize: function(options) {
            FrontendActionHeaderCell.__super__.initialize.call(this, options);

            if (!this.column.get('label')) {
                this.column.set(
                    'label',
                    __(options.themeOptions?.label ?? 'oro_frontend.datagrid.action_column.label')
                );
            }
        },

        getTemplateData: function() {
            const data = FrontendActionHeaderCell.__super__.getTemplateData.call(this);

            data.label = textUtil.abbreviate(this.column.get('label'), this.minWordsToAbbreviate);
            this.isLabelAbbreviated = data.label !== this.column.get('label');

            return data;
        },

        render: function() {
            this.$el.attr({
                scope: 'col'
            });

            const tplData = this.getTemplateData();

            FrontendActionHeaderCell.__super__.render.call(this);

            const panel = this.subview('actionsPanel');

            if (!panel.haveActions()) {
                this.$el.addClass('action-column--disabled');
                this.$el.empty().addClass('text-center');
                this.$el.append(actionHeaderCellLabel(tplData));
            }

            util.headerCellAbbreviateHint(this);

            return this;
        }
    });
    return FrontendActionHeaderCell;
});
