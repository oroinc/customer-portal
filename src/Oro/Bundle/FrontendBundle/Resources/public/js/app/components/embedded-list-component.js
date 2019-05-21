define(function(require) {
    'use strict';

    var EmbeddedListComponent;
    var BaseComponent = require('oroui/js/app/components/base/component');
    var mediator = require('oroui/js/mediator');
    var $ = require('jquery');

    /**
     * Fires oro:embedded-list show and click events for embedded list items.
     */
    EmbeddedListComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            itemSelector: '.embedded-list__item'
        },

        /**
         * @property {jQuery}
         */
        $initItems: null,

        /**
         * @inheritDoc
         */
        constructor: function EmbeddedListComponent() {
            EmbeddedListComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @param {Object} options
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            this.$el = options._sourceElement;

            this.$initItems = this.$el.find(this.options.itemSelector);
        },

        /**
         * @inheritDoc
         */
        delegateListeners: function() {
            EmbeddedListComponent.__super__.delegateListeners.apply(this, arguments);

            mediator.once('page:afterChange', this._afterChange.bind(this));
        },

        _afterChange: function() {
            this.trigger('oro:embedded-list:shown', this.$initItems);

            this.$el.on('click', this.options.itemSelector + ' a', this._onClickLink.bind(this));
        },

        _onClickLink: function(event) {
            // Not handle link without new url ("javascript:void(null)", "#" and equal)
            if (event.currentTarget.protocol !== window.location.protocol
                || (
                    event.currentTarget.pathname === window.location.pathname
                    && event.currentTarget.search === window.location.search
                )) {
                return;
            }

            var clickedItem = $(event.target).parents(this.options.itemSelector);
            this.trigger('oro:embedded-list:clicked', clickedItem, event);
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$el.off('click', this.options.itemSelector + ' a', this._onClickLink.bind(this));

            EmbeddedListComponent.__super__.dispose.apply(this, arguments);
        }
    });

    return EmbeddedListComponent;
});
