define(function(require) {
    'use strict';

    var EmptyItemsComponent;
    var BaseComponent = require('oroui/js/app/components/base/component');
    var mediator = require('oroui/js/mediator');
    var _ = require('underscore');
    var $ = require('jquery');

    EmptyItemsComponent = BaseComponent.extend({
        hideElements: '[data-name="empty-items__hide"]',
        /**
         * @property {Object}
         */
        options: {
            eventName: 'item:delete',
            hiddenClass: 'hidden'
        },
        /**
         * @inheritDoc
         */
        constructor: function EmptyItemsComponent() {
            EmptyItemsComponent.__super__.constructor.apply(this, arguments);
        },
        /**
         * @param {Object} options
         */
        initialize: function(options) {
            this.options = _.extend(this.options, options);
            this.$el = options._sourceElement;

            mediator.on(this.options.eventName, this.showEmptyMessage, this);
        },
        showEmptyMessage: function() {
            var itemsSelector = this.$el.data('items-selector') || '.itemsSelectorContainer';
            var emptyBlockSelector = this.$el.data('empty-block-selector') || '.emptyBlockSelectorContainer';
            if (this.$el.find(itemsSelector).length === 0) {
                this.$el.remove();
                $(emptyBlockSelector).removeClass(this.options.hiddenClass);
                $(this.hideElements).remove();
            }
        },
        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }
            delete this.hideElements;

            mediator.off(this.options.eventName, this.showEmptyMessage, this);

            EmptyItemsComponent.__super__.dispose.call(this);
        }
    });

    return EmptyItemsComponent;
});
