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

        this.update();
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
        const {clientHeight, scrollHeight, scrollTop} = target;

        target?.classList.toggle(blockStartClass, scrollTop > 0);
        target?.classList.toggle(blockEndClass, clientHeight + scrollTop < scrollHeight);
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
