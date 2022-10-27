define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const $ = require('jquery');

    const ToggleActionView = BaseView.extend({
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
         * @inheritdoc
         */
        constructor: function ToggleActionView(options) {
            ToggleActionView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.$el.on('click' + this.eventNamespace(), this.toggle.bind(this));

            ToggleActionView.__super__.initialize.call(this, options);
        },

        setElement: function(element) {
            this.$document = $(document);

            if (this.target) {
                this.target = $(this.target);
            }

            return ToggleActionView.__super__.setElement.call(this, element);
        },

        delegateEvents: function(events) {
            ToggleActionView.__super__.delegateEvents.call(this, events);

            this.$document.on('click' + this.eventNamespace(), this.onClickOverlay.bind(this));

            return this;
        },

        undelegateEvents: function() {
            if (this.$document) {
                this.$document.off(this.eventNamespace());
            }

            ToggleActionView.__super__.undelegateEvents.call(this);
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
            return ToggleActionView.__super__.dispose.call(this);
        }
    });

    return ToggleActionView;
});
