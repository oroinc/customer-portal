define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const ElementsHelper = require('orofrontend/js/app/elements-helper');
    const mediator = require('oroui/js/mediator');
    const _ = require('underscore');
    const $ = require('jquery');

    const FooterAlignView = BaseView.extend(_.extend({}, ElementsHelper, {
        elements: {
            items: '',
            footer: ''
        },

        timeout: 40,

        /**
         * @inheritdoc
         */
        constructor: function FooterAlignView(options) {
            FooterAlignView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            if (options.timeout) {
                this.timeout = options.timeout;
            }
            this.initializeElements(options);

            FooterAlignView.__super__.initialize.call(this, options);
        },

        /**
         * @inheritdoc
         */
        delegateEvents: function(events) {
            FooterAlignView.__super__.delegateEvents.call(this, events);
            mediator.on('layout:reposition', _.debounce(this.alignFooter, this.timeout), this);
            return this;
        },

        /**
         * @inheritdoc
         */
        undelegateEvents: function() {
            mediator.off(null, null, this);
            return FooterAlignView.__super__.undelegateEvents.call(this);
        },

        alignFooter: function() {
            this.clearElementsCache();

            _.each(this.getItemsByRow(), this.setAlign, this);
        },

        getItemsByRow: function() {
            const itemsByRow = [];
            let items;
            let previousOffset = 0;

            _.each(_.isRTL()
                ? this.getElement('items').get().reverse()
                : this.getElement('items'), function(item) {
                const $item = $(item);

                const $footer = $item.find(this.elements.footer);
                if (!$footer.length) {
                    return;
                }

                const offset = $footer.offset();
                if (!items || offset.left <= previousOffset) {
                    items = [];
                    itemsByRow.push(items);
                }
                previousOffset = offset.left;

                items.push({
                    $footer: $footer.css('padding-top', 0),
                    height: offset.top + $footer.outerHeight(true)
                });
            }, this);

            return itemsByRow;
        },

        setAlign: function(items) {
            if (items.length < 2) {
                return;
            }
            const maxHeight = _.max(items, function(item) {
                return item.height;
            }).height;

            _.each(items, function(item) {
                const changeHeight = maxHeight - item.height;
                if (changeHeight) {
                    item.$footer.css('padding-top', changeHeight);
                }
            });
        }
    }));

    return FooterAlignView;
});
