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
         * @inheritDoc
         */
        constructor: function FooterAlignView(options) {
            FooterAlignView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            if (options.timeout) {
                this.timeout = options.timeout;
            }
            this.initializeElements(options);

            FooterAlignView.__super__.initialize.call(this, options);
        },

        /**
         * @inheritDoc
         */
        delegateEvents: function(events) {
            FooterAlignView.__super__.delegateEvents.call(this, events);
            mediator.on('layout:reposition', _.debounce(this.alignFooter, this.timeout), this);
            return this;
        },

        /**
         * @inheritDoc
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
            let previousLeft = 0;

            _.each(this.getElement('items'), function(item) {
                const $item = $(item);

                const $footer = $item.find(this.elements.footer);
                if (!$footer.length) {
                    return;
                }

                const offset = $footer.offset();
                if (!items || offset.left <= previousLeft) {
                    items = [];
                    itemsByRow.push(items);
                }
                previousLeft = offset.left;

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
