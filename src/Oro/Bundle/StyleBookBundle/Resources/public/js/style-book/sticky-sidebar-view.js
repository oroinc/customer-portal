define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const mediator = require('oroui/js/mediator');
    const _ = require('underscore');
    const $ = require('jquery');

    const StickySidebarView = BaseView.extend({
        /**
         * @property
         */
        keepElement: true,

        /**
         * @property
         */
        optionNames: BaseView.prototype.optionNames.concat([
            'toggleClass', 'excludeTarget',
            'offsetTop', 'offsetBottom'
        ]),

        toggleClass: 'sticked',

        excludeTarget: null,

        offsetTop: 0,

        offsetBottom: 0,

        scrollTimeout: 60,

        /**
         * @inheritdoc
         */
        constructor: function StickySidebarView(options) {
            StickySidebarView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.$el.addClass(this.toggleClass);
            this.setPosition();

            StickySidebarView.__super__.initialize.call(this, options);
        },

        getExcludeTargetHeight: function() {
            return this.excludeTarget ? this.excludeTarget.outerHeight() : 0;
        },

        setElement: function(element) {
            this.$document = $(document);

            if (this.excludeTarget) {
                this.excludeTarget = $(this.excludeTarget);
            }

            return StickySidebarView.__super__.setElement.call(this, element);
        },

        setPosition: function() {
            const top = this.getExcludeTargetHeight() + this.offsetTop;
            this.$el.css({
                top: top,
                maxHeight: 'calc(100vh - ' + (top + this.offsetBottom) + 'px)'
            });
        },

        delegateListeners: function() {
            StickySidebarView.__super__.delegateListeners.call(this);
            this.listenTo(mediator, 'layout:reposition', this.setPosition.bind(this));
        },

        delegateEvents: function(events) {
            StickySidebarView.__super__.delegateEvents.call(this, events);

            this.$document.on(
                'scroll' + this.eventNamespace(),
                _.throttle(this.setPosition.bind(this), this.scrollTimeout)
            );

            return this;
        },

        undelegateEvents: function() {
            if (this.$document) {
                this.$document.off(this.eventNamespace());
            }

            StickySidebarView.__super__.undelegateEvents.call(this);
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$el.removeClass(this.toggleClass)
                .css({
                    top: '',
                    maxHeight: ''
                });

            this.undelegateEvents();
            return StickySidebarView.__super__.dispose.call(this);
        }
    });

    return StickySidebarView;
});
