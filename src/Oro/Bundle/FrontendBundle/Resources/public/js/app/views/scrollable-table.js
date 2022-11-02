define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const mediator = require('oroui/js/mediator');
    const _ = require('underscore');
    const $ = require('jquery');

    const ScrollableTableView = BaseView.extend({
        defaults: {
            head: '[data-scrollable-content-head]',
            body: '[data-scrollable-content-body]',
            content: '[data-scrollable-content]',
            innerContent: '[data-scrollable-inner-content]',
            itemHasOffset: '[data-scrollable-item-has-offset]',
            offset: 8
        },

        /**
         * @inheritdoc
         */
        constructor: function ScrollableTableView(options) {
            ScrollableTableView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            ScrollableTableView.__super__.initialize.call(this, options);

            this.render();
        },

        render: function() {
            this.$tableHeadItems = this.$el.find(this.defaults.head).children();
            this.$tableBodyItems = this.$el.find(this.defaults.body).children();

            this.hasScroll();
            this.alignCell();

            mediator.on('scrollable-table:reload', () => {
                this.hasScroll();
                this.alignCell();
            });

            $(window).on('resize', _.debounce(() => {
                this.alignCell();
            }, 200));
        },

        alignCell: function() {
            const self = this;

            this.$tableBodyItems.each(function(index) {
                self.$tableHeadItems
                    .eq(index)
                    .width($(this).width());
            });
        },

        hasScroll: function() {
            const self = this;
            const $scrollableContent = this.$el.find(this.defaults.content);
            const $scrollableInnerContent = this.$el.find(this.defaults.innerContent);
            const $itemHasOffset = this.$el.find(this.defaults.itemHasOffset);

            // The browser settings should the inner scroll
            if ($scrollableInnerContent.width() < $scrollableContent.width()) {
                // Has scroll
                if ($scrollableInnerContent.width() > $scrollableContent.height()) {
                    const scrollWidth = $scrollableContent.width() - $scrollableInnerContent.width();

                    $itemHasOffset.each(function(index) {
                        $(this).css({
                            'padding-right': index === 0 ? scrollWidth : self.defaults.offset
                        });
                    });
                }
            }
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }
            mediator.off('scrollable-table:reload');
            $(window).off('resize', this.alignCell());

            ScrollableTableView.__super__.dispose.call(this);
        }
    });

    return ScrollableTableView;
});
