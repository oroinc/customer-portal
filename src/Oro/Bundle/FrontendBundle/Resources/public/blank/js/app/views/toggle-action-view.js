define(function(require) {
    'use strict';

    var ToggleActionView;
    var BaseView = require('oroui/js/app/views/base/view');
    var _ = require('underscore');
    var $ = require('jquery');

    ToggleActionView = BaseView.extend({
        /**
         * @property
         */
        optionNames: BaseView.prototype.optionNames.concat([
            'toggleClass', 'target'
        ]),

        /**
         * @property
         */
        keepElement: true,

        /**
         * @property
         */
        toggleClass: 'open',

        /**
         * @property
         */
        target: null,

        /**
         * @inheritDoc
         */
        constructor: function ToggleActionView() {
            ToggleActionView.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.$el.on('click' + this.eventNamespace(), _.bind(this.toggle, this));

            ToggleActionView.__super__.initialize.apply(this, arguments);
        },

        setElement: function() {
            this.$document = $(document);

            if (this.target) {
                this.target = $(this.target);
            }

            return ToggleActionView.__super__.setElement.apply(this, arguments);
        },

        delegateEvents: function() {
            ToggleActionView.__super__.delegateEvents.apply(this, arguments);

            this.$document.on('click' + this.eventNamespace(), _.bind(this.onClickOverlay, this));

            return this;
        },

        undelegateEvents: function() {
            if (this.$document) {
                this.$document.off(this.eventNamespace());
            }

            ToggleActionView.__super__.undelegateEvents.apply(this, arguments);
        },

        toggle: function(state) {
            this.target.toggleClass(this.toggleClass, state);
        },

        onClickOverlay: function(event) {
            if (!$(event.target).closest(this.$el).length &&
                !$(event.target).closest(this.target).length) {
                this.toggle(false);
            }
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.toggle(false);

            this.$el.off(this.eventNamespace());
            this.undelegateEvents();
            return ToggleActionView.__super__.dispose.apply(this, arguments);
        }
    });

    return ToggleActionView;
});
