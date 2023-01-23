import {isRTL} from 'underscore';
import BaseView from 'oroui/js/app/views/base/view';
import viewportManager from 'oroui/js/viewport-manager';
import ScrollShadowView from 'orofrontend/js/app/views/scroll-shadow-view';

const MenuTravelingView = BaseView.extend({
    /**
     * @property {Object}
     */
    options: {
        hoverPriority: false,
        currentClass: 'show',
        isHoverableClass: 'is-hoverable',
        closeTrigger: '[data-role="close"]',
        menuBarSelector: '[data-menu-bar]',
        rootItemSelector: '[data-main-menu-item="1"]',
        itemSelector: '[data-main-menu-item]',
        itemLabelSelector: '[data-name="menu-label"]',
        itemLinkSelector: '[role="menuitem"]',
        triggerNextSelector: '[data-go-to="next"]',
        triggerPrevSelector: '[data-go-to="prev"]',
        popupSelector: '[role="dialog"]',
        popupHeaderSelector: '[data-role="header"]',
        popupFooterSelector: '[data-role="footer"]'
    },

    events() {
        return {
            [`click ${this.options.closeTrigger}`]: 'onResetMenu',
            [`click ${this.options.triggerNextSelector}`]: 'onTriggerNextClick',
            [`touchmove ${this.options.triggerNextSelector}`]: 'onTriggerNextTouchMove',
            [`touchend ${this.options.triggerNextSelector}`]: 'onTriggerNextTouchEnd',
            [`click ${this.options.triggerPrevSelector}`]: 'goToPrevSection',
            [`focusin ${this.options.triggerNextSelector}`]: 'onItemFocusIn',
            [`mousedown ${this.options.triggerNextSelector}`]: 'onItemMouseDown',
            [`focusin ${this.options.itemLinkSelector}`]: 'onItemFocusIn',
            [`mousedown ${this.options.itemLinkSelector}`]: 'onItemMouseDown',
            [`mouseenter ${this.options.itemSelector}`]: 'onItemMouseEnter',
            [`touchstart ${this.options.itemSelector}`]: 'onItemTouchStart',
            [`mouseleave ${this.options.menuBarSelector}`]: 'onMenuBarMouseLeave',
            [`show.bs.tooltip [data-toggle="tooltip"]`]: 'onItemShowTooltip'
        };
    },

    listen: {
        'layout:reposition mediator': 'setCSSProperty',
        'viewport:change mediator': 'onViewportChange'
    },

    /** @property */
    currentItem: null,

    /** @property */
    triggerPrev: null,

    /** @property
     * Private flag to determine if page was scrolled on touch event
    */
    isTouchMoved: false,

    /** @property
     * Private flag to determine if page was touched
    */
    isTouched: false,

    /**
     * @inheritdoc
     */
    keepElement: true,

    /**
     * @inheritdoc
     */
    constructor: function MenuTravelingView(options) {
        MenuTravelingView.__super__.constructor.call(this, options);
    },

    preinitialize(options) {
        this.options = Object.assign({}, this.options, options);
    },

    /**
     * @param {Object} options
     */
    initialize(options) {
        this.triggerPrev = this.el.querySelector(this.options.triggerPrevSelector);

        this.togglePrevTrigger();

        this.setCSSProperty();

        this._removeHoverableClass();

        this.subview('scroll-shadow', new ScrollShadowView({
            el: this.el
        }));
    },

    delegateEvents() {
        MenuTravelingView.__super__.delegateEvents.call(this);

        // Add Listener for outside click
        this._onClickOutside = this._onClickOutside.bind(this);
        document.addEventListener('click', this._onClickOutside);
    },

    undelegateEvents() {
        MenuTravelingView.__super__.undelegateEvents.call(this);

        document.removeEventListener('click', this._onClickOutside);
    },

    /**
     * The method that should remove the hover class helper after the view has been initialized
     */
    _removeHoverableClass() {
        this.el.classList.remove(this.options.isHoverableClass);
    },

    _onClickOutside(event) {
        if (!this.el.contains(event.target)) {
            this.closeMenu();
        }
    },

    _getItemOffset(item) {
        if (isRTL()) {
            // When it is on RTL mode, calculate offset right
            const elOffsetRight = window.innerWidth - this.el.getBoundingClientRect().right;
            const itemOffsetRight = window.innerWidth - item.getBoundingClientRect().right;
            return itemOffsetRight - elOffsetRight;
        } else {
            return item.offsetLeft;
        }
    },

    closeMenu() {
        const {rootItemSelector, currentClass} = this.options;

        this.el.querySelectorAll(`${rootItemSelector}.${currentClass}`).forEach(item => this.toggleItem(item, false));

        this.togglePrevTrigger();
    },

    onResetMenu() {
        this.el.querySelectorAll(`.${this.options.currentClass}`).forEach(item => this.toggleItem(item, false));

        this.currentItem = null;

        this.togglePrevTrigger();
    },

    onItemShowTooltip(event) {
        const textElement = event.target.querySelector(this.options.itemLabelSelector);

        // Skip showing tooltip if text label isn't overflowed
        if (!textElement || !(textElement.offsetWidth < textElement.scrollWidth)) {
            return false;
        }
    },

    onTriggerNextClick(event) {
        if (!this.options.hoverPriority || viewportManager.isApplicable('tablet')) {
            this.goToNextSection(event);
        }
    },

    onTriggerNextTouchMove() {
        this.isTouchMoved = true;
    },

    onTriggerNextTouchEnd(event) {
        // Skip TouchEnd event if page has been scrolled
        if (this.isTouchMoved) {
            this.isTouchMoved = false;
            return;
        }

        if (event.cancelable) {
            event.preventDefault();
        }

        this.goToNextSection(event);
    },

    /**
     * Prevent focusin handler if user clicked by mouse
     * @param {Event} event
     */
    onItemMouseDown(event) {
        event.preventDefault();
    },

    onItemFocusIn(event) {
        event.stopPropagation();
        const currentItem = event.currentTarget.closest(this.options.itemSelector);
        if (this.getItemLevel(currentItem) === 2) {
            this.closeNonRelatedItems(currentItem);
            this.toggleItem(currentItem, false);
        }
    },

    onItemMouseEnter(event) {
        // Skip mouseenter if it was touched
        if (this.isTouched) {
            return;
        }

        if (!this.options.hoverPriority || viewportManager.isApplicable('tablet')) {
            return;
        }

        event.stopPropagation();

        this.goToNextSection(event);
    },

    onItemTouchStart(event) {
        event.stopPropagation();

        if (!this.isTouched) {
            this.isTouched = true;

            setTimeout(() => this.isTouched = false, 300);
        }
    },

    onMenuBarMouseLeave(event) {
        if (!this.options.hoverPriority || viewportManager.isApplicable('tablet')) {
            return;
        }

        this.closeMenu();
    },

    /**
     * Handler of the next trigger
     * @param {Object} event
     */
    goToNextSection(event) {
        this.setCSSProperty();

        switch (this.getItemLevel(event.currentTarget)) {
            case 1:
                this.goToNextRoot(event);
                break;
            default:
                this.goToNextDefault(event);
                break;
        }

        this.togglePrevTrigger();
    },

    goToNextRoot(event) {
        if (viewportManager.isApplicable('mobile-big')) {
            this.goToNextDefault(event);
        } else {
            const currentItem = event.currentTarget.closest(this.options.itemSelector);

            if (this.isItemActive(currentItem)) {
                this.toggleItem(currentItem, false);
            } else {
                // Try to find previous saved item with current state
                this.currentItem = currentItem.querySelector(`.${this.options.currentClass}`) || this.currentItem;

                if (!currentItem.contains(this.currentItem)) {
                    // If no one sub-menu is open try to find and open first one or only current
                    this.currentItem = currentItem.querySelector(this.options.itemSelector) || currentItem;
                }

                this.closeNonRelatedItems(this.currentItem);
                this.highlightBranchUp(this.currentItem);

                this.toggleItem(this.currentItem, true);
            }
        }
    },

    goToNextDefault(event) {
        this.currentItem = event.currentTarget.closest(this.options.itemSelector);

        this.closeNonRelatedItems(this.currentItem);
        this.highlightBranchUp(this.currentItem);

        this.toggleItem(this.currentItem, true);
    },

    closeNonRelatedItems(target) {
        const {itemSelector, currentClass} = this.options;
        this.el.querySelectorAll(`${itemSelector}.${currentClass}`).forEach(item => {
            if (!item.contains(target)) {
                this.toggleItem(item, false);
            }
        });
    },

    toggleItem(item, force) {
        item.classList.toggle(this.options.currentClass, force);
    },

    highlightBranchUp(item) {
        let _parentItem = item.parentNode.closest(this.options.itemSelector);

        while (_parentItem) {
            this.toggleItem(_parentItem, true);

            _parentItem = _parentItem.parentNode.closest(this.options.itemSelector);
        }
    },

    isItemActive(item) {
        return item.classList.contains(this.options.currentClass);
    },

    getItemLevel(item) {
        return parseInt(item.closest(this.options.itemSelector)?.dataset.mainMenuItem);
    },

    /**
     * Handler of the prev trigger
     */
    goToPrevSection() {
        if (!viewportManager.isApplicable('mobile-big')) {
            this.currentItem = this.currentItem.closest(this.options.rootItemSelector);
        }

        this.toggleItem(this.currentItem, false);

        this.currentItem = this.currentItem.parentNode.closest(this.options.itemSelector);

        this.togglePrevTrigger();
    },

    togglePrevTrigger() {
        const {rootItemSelector, currentClass} = this.options;

        return this.triggerPrev.classList.toggle(
            'hidden', !this.el.querySelector(`${rootItemSelector}.${currentClass}`)
        );
    },

    setCSSProperty() {
        if (this.disposed) {
            return;
        }

        const {
            popupSelector,
            popupHeaderSelector,
            popupFooterSelector
        } = this.options;

        const popup = this.el.closest(popupSelector);
        let offsetTop = 0;
        let offsetBottom = 0;

        if (popup) {
            const popupHeader = popup.querySelector(popupHeaderSelector);
            const popupFooter = popup.querySelector(popupFooterSelector);

            offsetTop = popupHeader?.offsetHeight || 0;
            offsetBottom = popupFooter?.offsetHeight || 0;
        } else {
            const elRect = this.el.getBoundingClientRect();
            offsetBottom = elRect.top + elRect.height;

            // If menu is not sticky than add scroll compensation
            if (!this.el.closest('.in-sticky')) {
                offsetBottom += window.scrollY;
            }
        }

        this.el.style.setProperty('--main-menu-offset-top', `${offsetTop}px`);
        this.el.style.setProperty('--main-menu-offset-bottom', `${offsetBottom}px`);

        if (!viewportManager.isApplicable('tablet')) {
            this.el.querySelectorAll(`${this.options.rootItemSelector}`).forEach(item => {
                const offset = this._getItemOffset(item);

                item.style.setProperty('--main-menu-offset-start', `${offset}px`);
                item.style.setProperty('--main-menu-offset-width', `${item.offsetWidth}px`);
            });
        }
    },

    onViewportChange() {
        this.setCSSProperty();
    }
});

export default MenuTravelingView;
