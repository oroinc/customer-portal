define(function(require) {
    'use strict';

    var ScrollTopView;
    var viewportManager = require('oroui/js/viewport-manager');
    var BaseView = require('oroui/js/app/views/base/view');
    var _ = require('underscore');
    var $ = require('jquery');
    var module = require('module');

    var config = module.config();

    config = _.extend({
        togglePoint: 165,
        duration: 500,
        easing: 'swing',
        allowLanding: true,
        bottomOffset: 20,
        props: {
            scrollTop: 0
        }
    }, config);

    ScrollTopView = BaseView.extend({
        autoRender: true,

        options: {
            togglePoint: config.togglePoint,
            duration: config.duration,
            easing: config.easing,
            allowLanding: config.allowLanding,
            bottomOffset: config.bottomOffset,
            props: config.props
        },

        events: {
            click: 'scrollTop'
        },

        listen: {
            'viewport:change mediator': 'render'
        },

        /**
         * @property {Boolean}
         */
        isApplicable: false,

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.extend({}, this.options, options || {});
            ScrollTopView.__super__.initialize.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        setElement: function(element) {
            this.$window = $(window);
            this.$document = $('html, body');
            return ScrollTopView.__super__.setElement.call(this, element);
        },

        /**
         * @inheritDoc
         */
        delegateEvents: function() {
            ScrollTopView.__super__.delegateEvents.apply(this, arguments);
            this.$window.on('scroll' + this.eventNamespace(), _.debounce(_.bind(this.toggle, this), 5));
        },

        /**
         * @inheritDoc
         */
        undelegateEvents: function() {
            if (this.$window) {
                this.$window.off(this.eventNamespace());
            }
            ScrollTopView.__super__.undelegateEvents.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        render: function() {
            this.isApplicable = viewportManager.isApplicable(this.options.viewport);
            this.toggle();
        },

        toggle: function() {
            if (this.isApplicable) {
                var state = this.$window.scrollTop() > this.options.togglePoint;
                this.$el.toggle(state);
                this.land();
            } else {
                this.$el.hide();
            }
        },

        scrollTop: function() {
            this.$document
                .stop(true, true)
                .animate(this.options.props, this.options.duration, this.options.easing);
        },

        land: function() {
            if (!this.options.allowLanding) {
                return;
            }
            var footerHeight = this.$document.find('[data-page-footer]').height();
            var windowHeight = this.$window.height();
            var elementHeight = this.$el.height() + this.options.bottomOffset;
            var scrollY = this.$document.height() - this.$window.scrollTop();
            var footerOffset = footerHeight + windowHeight + elementHeight;
            this.$el.toggleClass('scroll-top--landed', footerOffset >= scrollY);
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.undelegateEvents();

            delete this.$window;
            delete this.$document;

            ScrollTopView.__super__.dispose.call(this);
        }
    });

    return ScrollTopView;
});
