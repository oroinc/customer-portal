import BaseView from 'oroui/js/app/views/base/view';

const ScrollShadowView = BaseView.extend({
    /**
     * @property {Object}
     */
    options: {
        scrollTarget: '[data-scroll-target]',
        blockStartClass: 'shadow-start',
        blockEndClass: 'shadow-end'
    },

    listen: {
        'viewport:change mediator': 'setScrollOffsets',
        'layout:reposition mediator': 'setScrollOffsets'
    },

    /**
     * @inheritdoc
     */
    autoRender: false,

    /**
     * @inheritdoc
     */
    keepElement: true,

    /**
     * @property {NodeList}
     */
    scrollTargets: null,

    /**
     * Observer for detection changes visibility and resizing of scrollTargets
     * @property {ResizeObserver}
     */
    _resizeObserver: null,

    /**
     * @inheritdoc
     */
    constructor: function ScrollShadowView(options) {
        ScrollShadowView.__super__.constructor.call(this, options);
    },

    preinitialize() {
        this.scrollTargets = [];
        this.onScroll = this.onScroll.bind(this);
    },

    initialize(options) {
        this.options = {...this.options, ...options};

        if (this.el.matches(this.options.scrollTarget)) {
            this.scrollTargets.push(this.el);
        }

        this.scrollTargets.push(...this.el.querySelectorAll(this.options.scrollTarget));

        this._resizeObserver = new ResizeObserver(entries => {
            for (const entry of entries) {
                this.addShadow(entry.target);
            }
        });

        this.setScrollOffsets();
        this.update();

        // Making an element to be scrollable in a while
        if (this.el.classList.contains('start-scroll-from-end')) {
            setTimeout(() => {
                this.el.classList.remove('start-scroll-from-end');
            }, 0);
        }
    },

    update() {
        if (this.disposed) {
            return;
        }

        this.delegateEvents();
        this.addShadows();
    },

    delegateEvents() {
        ScrollShadowView.__super__.delegateEvents.call(this);

        this.scrollTargets?.forEach(target => {
            target.addEventListener('scroll', this.onScroll);

            if (this._resizeObserver) {
                this._resizeObserver.observe(target);
            }
        });

        return this;
    },

    undelegateEvents() {
        ScrollShadowView.__super__.undelegateEvents.call(this);

        this.scrollTargets?.forEach(target => {
            target.removeEventListener('scroll', this.onScroll);
        });

        if (this._resizeObserver) {
            this._resizeObserver.disconnect();
        }

        return this;
    },

    onScroll(event) {
        this.addShadow(event.target);
    },

    addShadows() {
        if (Array.isArray(this.scrollTargets) === false) {
            return;
        }
        this.scrollTargets.forEach(target => this.addShadow(target));
    },

    addShadow(target) {
        const {blockStartClass, blockEndClass} = this.options;
        const {clientHeight, clientWidth, scrollHeight, scrollWidth, scrollTop, scrollLeft} = target;
        const hasVerticalScrollbar = target.scrollHeight > target.clientHeight;
        const hasHorizontalScrollbar = target.scrollWidth > target.clientWidth;

        target.classList.toggle('horizontal-scrolling', hasHorizontalScrollbar);

        if (hasHorizontalScrollbar) {
            target?.classList.toggle(blockStartClass, scrollLeft > 0);
            target?.classList.toggle(blockEndClass, clientWidth + scrollLeft < scrollWidth);
        }

        target.classList.toggle('vertical-scrolling', hasVerticalScrollbar);

        if (hasVerticalScrollbar) {
            target?.classList.toggle(blockStartClass, scrollTop > 0);
            target?.classList.toggle(blockEndClass, clientHeight + scrollTop < scrollHeight);
        }

        if (!hasHorizontalScrollbar && !hasVerticalScrollbar) {
            target?.classList.toggle(blockStartClass, false);
            target?.classList.toggle(blockEndClass, false);
        }
    },

    removeShadows() {
        if (Array.isArray(this.scrollTargets) === false) {
            return;
        }
        this.scrollTargets.forEach(element => this.removeShadow(element));
    },

    removeShadow(target) {
        const {blockStartClass, blockEndClass} = this.options;

        target.shadowEl?.classList.remove([blockStartClass, blockEndClass]);
    },

    setScrollOffsets() {
        for (const [side, selector] of Object.entries(this.options.scrollOffsets || {})) {
            let value = this.$(selector).outerHeight(true);

            if (['left', 'right'].includes(side)) {
                value = this.$(selector).outerWidth(true);
            }

            this.el.style.setProperty(`--shadow-start-${side}`, `${value}px`);
        }
    },

    /**
     * @inheritdoc
     */
    dispose() {
        if (this.disposed) {
            return;
        }

        this.removeShadows();
        delete this.scrollTargets;
        delete this._resizeObserver;

        return ScrollShadowView.__super__.dispose.call(this);
    }
});

export default ScrollShadowView;
