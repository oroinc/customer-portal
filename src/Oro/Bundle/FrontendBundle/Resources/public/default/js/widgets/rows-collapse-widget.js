define([
    'jquery',
    'oroui/js/mediator',
    'underscore',
    'oroui/js/widget/collapse-widget'
], function($, mediator, _) {
    'use strict';

    $.widget('oroui.rowCollapseWidget', $.oroui.collapseWidget, {
        options: $.extend({}, $.oroui.collapseWidget.options, {
            rowsCount: 0,
            visibleRows: 3,
            checkOverflow: true,
            rowSelector: 'tbody tr',
            headerSelector: 'thead'
        }),

        _init: function() {
            this._super();
            this.$trigger.show();
        },

        _initEvents: function() {
            this._super();
            mediator.on('viewport:change', this.onViewportChange, this);
        },

        _isOverflow: function() {
            return this.options.rowsCount > this.options.visibleRows;
        },

        _applyStateOnContainer: function(isOpen) {
            if (this.options.animationSpeed) {
                if (isOpen) {
                    this.$container.animate({
                        height: this.getRowsHeight()
                    }, this.options.animationSpeed, () => {
                        this.$container.css('overflow', 'visible');
                    });
                } else {
                    this.$container.animate({
                        height: this.getRowsHeight(this.options.visibleRows)
                    }, this.options.animationSpeed, () => {
                        this.$container.css('overflow', 'hidden');
                    });
                }
            }
        },

        _applyStateOnTrigger: function(isOpen) {
            if (isOpen) {
                this.$trigger
                    .find('[data-collapse-text]')
                    .text(_.__('oro_frontend.rows_collapse.trigger.label.normal'));
            } else {
                this.$trigger
                    .find('[data-collapse-text]')
                    .text(_.__('oro_frontend.rows_collapse.trigger.label.truncated', {
                        hiddenRows: this.calculateHiddenRows()
                    }));
            }
            this.$trigger.attr('aria-expanded', isOpen);
            this._super();
        },

        onViewportChange: function() {
            this._applyStateOnContainer(this.options.open);
        },

        calculateHiddenRows: function() {
            return this.options.rowsCount - this.options.visibleRows;
        },

        getRowsHeight: function(rows) {
            const self = this;
            const $rows = this.$el.find(self.options.rowSelector);
            let height = this.$el.find(self.options.headerSelector).outerHeight();
            const rowsCount = rows || $rows.length;

            $rows.each((index, row) => {
                if (index >= rowsCount) {
                    return;
                }
                height += $(row).outerHeight();
            });

            return height;
        }
    });

    return 'rowCollapseWidget';
});
