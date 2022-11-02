define(function(require) {
    'use strict';

    const BaseComponent = require('oroui/js/app/components/base/component');
    const mediator = require('oroui/js/mediator');
    const $ = require('jquery');
    const _ = require('underscore');

    /**
     * Fires oro:embedded-list show and click events for embedded list items.
     */
    const EmbeddedListComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            itemSelector: '[data-name="embedded-list-item"]'
        },

        /**
         * @property {jQuery}
         */
        $initItems: null,

        /**
         * @inheritdoc
         */
        constructor: function EmbeddedListComponent(options) {
            EmbeddedListComponent.__super__.constructor.call(this, options);
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
         * @inheritdoc
         */
        delegateListeners: function() {
            EmbeddedListComponent.__super__.delegateListeners.call(this);

            mediator.once('page:afterChange', this._afterChange.bind(this));
        },

        _afterChange: function() {
            this.trigger('oro:embedded-list:shown', this.$initItems);

            // Both click and mouseup needed to be able to track both left and middle buttons clicks.
            this.$el.on('click mouseup', this.options.itemSelector + ' a', this._onClickLink.bind(this));
        },

        _onClickLink: function(event) {
            if (event.type === 'mouseup' && event.which !== 2) {
                // Skips when mouseup triggered for the left mouse button, as it will be fired by click afterwards.
                return;
            }

            // Skips links without new url ("javascript:void(null)", "#" and equal)
            const link = event.currentTarget;
            if (link.protocol !== window.location.protocol ||
                (link.pathname === window.location.pathname && link.search === window.location.search)
            ) {
                return;
            }

            const clickedItem = $(event.currentTarget).parents(this.options.itemSelector);
            this.trigger('oro:embedded-list:clicked', clickedItem, event);
        },

        /**
         * @inheritdoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$el.off('click mouseup', this.options.itemSelector + ' a', this._onClickLink.bind(this));

            EmbeddedListComponent.__super__.dispose.call(this);
        }
    });

    return EmbeddedListComponent;
});
