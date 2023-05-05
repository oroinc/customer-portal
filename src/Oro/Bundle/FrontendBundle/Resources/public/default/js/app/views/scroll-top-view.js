define(function(require, exports, module) {
    'use strict';

    const viewportManager = require('oroui/js/viewport-manager').default;
    const BaseView = require('oroui/js/app/views/base/view');
    const _ = require('underscore');
    const $ = require('jquery');
    let config = require('module-config').default(module.id);

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

    const ScrollTopView = BaseView.extend({
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

        listen() {
            return {
                [`viewport:${this.options.viewport} mediator`]: 'render'
            };
        },

        /**
         * @property {Boolean}
         */
        isApplicable: false,

        /**
         * @inheritdoc
         */
        constructor: function ScrollTopView(options) {
            ScrollTopView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.options = _.extend({}, this.options, options || {});
            ScrollTopView.__super__.initialize.call(this, options);
        },

        /**
         * @inheritdoc
         */
        setElement: function(element) {
            this.$window = $(window);
            this.$document = $('html, body');
            return ScrollTopView.__super__.setElement.call(this, element);
        },

        /**
         * @inheritdoc
         */
        delegateEvents: function(events) {
            ScrollTopView.__super__.delegateEvents.call(this, events);
            this.$window.on('scroll' + this.eventNamespace(), _.debounce(this.toggle.bind(this), 5));
        },

        /**
         * @inheritdoc
         */
        undelegateEvents: function() {
            if (this.$window) {
                this.$window.off(this.eventNamespace());
            }
            ScrollTopView.__super__.undelegateEvents.call(this);
        },

        /**
         * @inheritdoc
         */
        render: function() {
            this.isApplicable = viewportManager.isApplicable(this.options.viewport);
            this.toggle();
        },

        toggle: function() {
            if (this.disposed) {
                return;
            }

            if (this.isApplicable && this.$window.scrollTop() > this.options.togglePoint) {
                this.$el.addClass('scroll-top-visible');
                this.$el.attr('aria-hidden', false);
                this.land();
            } else {
                this.$el.removeClass('scroll-top-visible');
                this.$el.attr('aria-hidden', true);
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
            const footerHeight = this.$document.find('[data-page-footer]').height();
            const windowHeight = this.$window.height();
            const elementHeight = this.$el.height() + this.options.bottomOffset;
            const scrollY = this.$document.height() - this.$window.scrollTop();
            const footerOffset = footerHeight + windowHeight + elementHeight;
            this.$el.toggleClass('scroll-top--landed', footerOffset >= scrollY);
        },

        /**
         * @inheritdoc
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
