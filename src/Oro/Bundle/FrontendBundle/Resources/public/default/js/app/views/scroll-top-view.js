define(function(require) {
    'use strict';

    var ScrollTopView;
    var viewportManager = require('oroui/js/viewport-manager');
    var BaseView = require('oroui/js/app/views/base/view');
    var mediator = require('oroui/js/mediator');
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

        /**
         * @property {jQuery.Element}
         */
        $element: null,

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
            this.$element = $(element);
            return ScrollTopView.__super__.setElement.call(this, element);
        },

        /**
         * @inheritDoc
         */
        delegateEvents: function() {
            this.$window.on('scroll', _.bind(this.toggle, this));
            this.$element.on('click', _.bind(this.scrollTop, this));
            mediator.on('viewport:change', this.render, this);
        },

        /**
         * @inheritDoc
         */
        undelegateEvents: function() {
            this.$window.off('scroll', this.toggle);
            this.$element.off('click', this.scrollTop);
            mediator.off(null, null, this);
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
                this.$element.toggle(state);
                this.land();
            } else {
                this.$element.hide();
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
            var $footer = this.$element.closest('body').find('footer');
            var footerHeight = $footer.height();
            var windowHeight = this.$window.height();
            var elementHeight = this.$element.height() + this.options.bottomOffset;
            var scrollY = this.$document.height() - this.$window.scrollTop();
            var footerOffset = footerHeight + windowHeight + elementHeight;
            this.$element.toggleClass('scroll-top--landed', footerOffset >= scrollY);
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.undelegateEvents();

            delete this.$element;

            ScrollTopView.__super__.dispose.call(this);
        }
    });

    return ScrollTopView;
});
