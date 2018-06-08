define(function(require) {
    'use strict';

    var StickySidebarView;
    var BaseView = require('oroui/js/app/views/base/view');
    var mediator = require('oroui/js/mediator');
    var _ = require('underscore');
    var $ = require('jquery');

    StickySidebarView = BaseView.extend({
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
         * @inheritDoc
         */
        constructor: function StickySidebarView() {
            StickySidebarView.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.$el.addClass(this.toggleClass);
            this.setPosition();

            StickySidebarView.__super__.initialize.apply(this, arguments);
        },

        getExcludeTargetHeight: function() {
            return this.excludeTarget ? this.excludeTarget.outerHeight() : 0;
        },

        setElement: function() {
            this.$document = $(document);

            if (this.excludeTarget) {
                this.excludeTarget = $(this.excludeTarget);
            }

            return StickySidebarView.__super__.setElement.apply(this, arguments);
        },

        setPosition: function() {
            var top = this.getExcludeTargetHeight() + this.offsetTop;
            this.$el.css({
                top: top,
                maxHeight: 'calc(100vh - ' + (top + this.offsetBottom) + 'px)'
            });
        },

        delegateListeners: function() {
            StickySidebarView.__super__.delegateListeners.apply(this, arguments);
            this.listenTo(mediator, 'layout:reposition', _.bind(this.setPosition, this));
        },

        delegateEvents: function() {
            StickySidebarView.__super__.delegateEvents.apply(this, arguments);

            this.$document.on(
                'scroll' + this.eventNamespace(),
                _.throttle(_.bind(this.setPosition, this), this.scrollTimeout)
            );

            return this;
        },

        undelegateEvents: function() {
            if (this.$document) {
                this.$document.off(this.eventNamespace());
            }

            StickySidebarView.__super__.undelegateEvents.apply(this, arguments);
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
            return StickySidebarView.__super__.dispose.apply(this, arguments);
        }
    });

    return StickySidebarView;
});
