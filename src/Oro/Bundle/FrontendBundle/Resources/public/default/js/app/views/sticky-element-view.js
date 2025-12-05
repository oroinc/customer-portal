import $ from 'jquery';
import {debounce, isFunction} from 'underscore';
import error from 'oroui/js/error';
import BaseView from 'oroui/js/app/views/base/view';

const NAME = 'stickyElement';
const DATA_KEY = 'oro.' + NAME;

const StickyElementView = BaseView.extend({
    optionNames: BaseView.prototype.optionNames.concat([
        'group', 'toggleClass', 'name', 'sentinel'
    ]),

    /**
     * Position group ['top', 'bottom']
     *
     * @property {string}
     */
    group: 'top',

    /**
     * @property {boolean}
     */
    isActive: false,

    /**
     * @property {string}
     */
    toggleClass: null,

    /**
     * @property {boolean}
     */
    isStuck: false,

    /**
     * @property {string}
     */
    sentinel: null,

    CHANGE_SCROLL_DIR_TIMEOUT: 200,

    prefix: 'sticky',

    listen: {
        'scroll:direction:change mediator': 'onChangeDirection',
        'layout:reposition mediator': 'update',
        'layout:content-relocated mediator': 'update',
        'widget_dialog:close mediator': 'update',
        'fullscreen:popup:close mediator': 'update',
        'page:afterChange mediator': 'update'
    },

    constructor: function StickyElementView(...args) {
        this.onChangeDirection = debounce(this.onChangeDirection.bind(this), this.CHANGE_SCROLL_DIR_TIMEOUT);
        StickyElementView.__super__.constructor.apply(this, args);
    },

    initialize(options) {
        this._createPlaceholder();
        this.update();

        StickyElementView.__super__.initialize.call(this, options);
    },

    /**
     * Create element placeholder to get original position in document
     *
     * @private
     */
    _createPlaceholder() {
        if (this.stickyPlaceholder) {
            return;
        }

        const placeholder = document.createElement('span');
        placeholder.classList.add('sticky-placeholder');
        placeholder.setAttribute('aria-hidden', true);

        if (this.group === 'top') {
            this.$el.before(placeholder);
        } else {
            this.$el.after(placeholder);
        }

        this.stickyPlaceholder = placeholder;
    },

    /**
     * @inheritDoc
     */
    delegateEvents(events) {
        StickyElementView.__super__.delegateEvents.call(this, events);

        $(document).on(`scroll${this.eventNamespace()}`, this.update.bind(this));
    },

    /**
     * @inheritDoc
     */
    undelegateEvents() {
        $(document).off(this.eventNamespace());
        return StickyElementView.__super__.undelegateEvents.call(this);
    },

    /**
     * Add specific class from scroll direction
     *
     * @param direction
     */
    onChangeDirection(direction) {
        if (direction) {
            this.$el
                .toggleClass('scroll-down', direction > 0)
                .toggleClass('scroll-up', direction < 0);
        }
    },

    /**
     * Update CSS variables, update sticky element state
     */
    update() {
        const stickyPlaceholderTop = this.stickyPlaceholder.offsetTop;
        this.isActive = this.isActiveElement();

        if (!this.isActive) {
            return;
        }

        this.isSticky();

        const {height} = this.el.getBoundingClientRect();

        if (this.name) {
            this.setRootVar(this.getRootVarName(`element-height`), `${height}px`);
            this.setRootVar(this.getRootVarName(`offset-${this.group}`), `${stickyPlaceholderTop}px`);
            this.setRootVar(
                this.getRootVarName(
                    `element-offset-${this.group}`),
                `${height + stickyPlaceholderTop}px`
            );
        }
    },

    /**
     * Remove dynamically added vars and classes
     */
    cleanup() {
        if (this.name) {
            this.setRootVar(this.getRootVarName(`group-offset-y`), null);
            this.setRootVar(this.getRootVarName(`offset-${this.group}`), null);
            this.setRootVar(this.getRootVarName(`element-offset-${this.group}`), null);
            this.setRootVar(this.getRootVarName(`element-height`), null);
        }

        this.el.classList.remove('is-sentinel');
        this.el.classList.remove('in-sticky');

        this.stickyPlaceholder.remove();
    },

    /**
     * Get current element position bounding position
     *
     * @returns {number}
     */
    getPos() {
        return this.el.getBoundingClientRect()[this.group];
    },

    /**
     * Get current element original position bounding position
     *
     * @returns {number}
     */
    getOriginalPos() {
        return this.stickyPlaceholder.getBoundingClientRect()[this.group];
    },

    /**
     * Detect if element is stuck
     * Detect if element sentinel destination
     *
     * @returns {boolean}
     */
    isSticky() {
        const rect = this.el.getBoundingClientRect();
        this.isStuck = this.group === 'top'
            ? this.getOriginalPos() < this.getPos()
            : this.getOriginalPos() > this.getPos();

        if (this.name && this.sentinel) {
            const offsetProp = this.getRootVar(`--sticky-${this.sentinel}-offset-${this.group}`);
            if (offsetProp) {
                const offset = parseInt(offsetProp);
                const sentinel = (this.getOriginalPos() + offset) < 0;
                if (this.getOriginalPos() + offset + rect.height < 0) {
                    this.setRootVar(this.getRootVarName(`group-offset-y`), `${rect.height}px`);
                } else {
                    this.setRootVar(this.getRootVarName(`group-offset-y`), null);
                }

                this.el.classList.toggle('is-sentinel', sentinel);
            }
        }

        this.el.classList.toggle('in-sticky', this.isStuck);

        if (this.toggleClass) {
            this.$el.toggleClass(this.toggleClass, this.isStuck);
        }

        return this.isStuck;
    },

    /**
     * Is element ready for sticky
     *
     * @returns {boolean}
     */
    isActiveElement() {
        return this.$el.css('position') === 'sticky' && this.$el.is(':visible') && !this.$el.is(':empty');
    },

    /**
     * Create global variable name
     *
     * @param {string} varName
     * @returns {string}
     */
    getRootVarName(varName) {
        if (!this.name) {
            console.error('Need define `name` for sticky element');
        }

        return `--${this.prefix}-${this.name}-${varName}`;
    },

    /**
     * Set root variable to global access
     *
     * @param {string} name
     * @param {string|number|null|undefined} value
     */
    setRootVar(name, value) {
        const root = document.querySelector(':root');

        if (!value) {
            root.style.removeProperty(name);
        }

        root.style.setProperty(name, value);
    },

    /**
     * Get global variable
     *
     * @param {string} name
     * @returns {string}
     */
    getRootVar(name) {
        return getComputedStyle(document.querySelector(':root'))
            .getPropertyValue(name);
    },

    /**
     * @inheritDoc
     */
    dispose() {
        if (this.disposed) {
            return;
        }

        this.cleanup();
        this.$el.removeData(DATA_KEY);

        StickyElementView.__super__.dispose.call(this);
    }
});

$.fn[NAME] = function(options, ...args) {
    let response = this;

    this.each(index => {
        const $element = $(this);
        const instance = $element.data(DATA_KEY);

        if (!instance) {
            $element.data(DATA_KEY, new StickyElementView({
                el: this,
                ...options
            }));
            return;
        }

        if (typeof options === 'string') {
            if (options === 'instance') {
                response = instance;
                return response;
            }

            if (!isFunction(instance[options]) || options.charAt(0) === '_') {
                error.showErrorInConsole(new Error('Instance ' + NAME + ' doesn\'t support method ' + options ));
                return false;
            }

            const result = instance[options](...args);

            if (result !== void 0 && index === 0) {
                response = result;
            }
        }
    });

    return response;
};

$.fn[NAME].constructor = StickyElementView;

export default StickyElementView;
