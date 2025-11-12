import viewportManager from 'oroui/js/viewport-manager';
import BaseView from 'oroui/js/app/views/base/view';
import _ from 'underscore';
import $ from 'jquery';
import moduleConfig from 'module-config';
const config = {
    toggleFactor: 4, // number of viewport heights to set threshold for scroll-to-top button to appear
    duration: 500,
    easing: 'swing',
    allowLanding: true,
    bottomOffset: 20,
    parentElement: '[data-role="page-main-container"]',
    props: {
        scrollTop: 0
    },
    ...moduleConfig(module.id)
};

const INTERSECTION_OFFSET_CSS_VAR = '--scroll-top-intersection-offset';

const ScrollTopView = BaseView.extend({
    autoRender: true,

    options: {
        toggleFactor: config.toggleFactor,
        duration: config.duration,
        easing: config.easing,
        allowLanding: config.allowLanding,
        bottomOffset: config.bottomOffset,
        parentElement: config.parentElement,
        props: config.props
    },

    events: {
        click: 'scrollTop'
    },

    intersectWith: '.sticky--bottom, [data-bottom-bar]',

    listen() {
        return {
            'layout:reposition mediator': 'adjustPosition',
            'content:shown mediator': 'adjustPosition',
            'content:hidden mediator': 'adjustPosition',
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

        const threshold = Math.ceil(
            (this.$window.height() * this.options.toggleFactor) + $(this.options.parentElement).position().top
        );

        if (this.isApplicable && this.$document.scrollTop() > threshold) {
            this.$el.addClass('scroll-top-visible');
            this.$el.attr('aria-hidden', false);
            this.land();
            this.adjustPosition();
        } else {
            this.$el.removeClass('scroll-top-visible');
            this.$el.attr('aria-hidden', true);
        }
    },

    adjustPosition() {
        document.body.style.removeProperty(INTERSECTION_OFFSET_CSS_VAR);

        if (!this.$el.is(':visible') || this.$el.is('.scroll-top--landed')) {
            return;
        }

        const $list = $(this.intersectWith).filter((i, el) => $(el).is(':visible') && el.offsetHeight);

        if (!$list.length) {
            return;
        }

        const highestEl = $list.toArray().reduce((highestEl, el) => {
            return el.getBoundingClientRect().top < highestEl.getBoundingClientRect().top ? el : highestEl;
        });
        const intersectionOffset = parseInt(
            getComputedStyle(document.body).getPropertyValue(INTERSECTION_OFFSET_CSS_VAR)
        ) || 0;
        const referenceRect = highestEl.getBoundingClientRect();
        const newOffset =
            visualViewport.height - referenceRect.top + parseInt($(highestEl).data('bottom-bar') || 0);
        if (intersectionOffset === newOffset) {
            return;
        }

        const scrollTopRect = this.el.getBoundingClientRect();
        const isOverlapping =
            scrollTopRect.right >= referenceRect.left &&
            scrollTopRect.left <= referenceRect.right &&
            scrollTopRect.bottom >= referenceRect.top &&
            scrollTopRect.top <= visualViewport.height;

        if (isOverlapping) {
            document.body.style.setProperty(INTERSECTION_OFFSET_CSS_VAR, `${newOffset}px`);
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
        const footerHeight = this.$document.find('[data-page-footer]').outerHeight(true);
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

export default ScrollTopView;
