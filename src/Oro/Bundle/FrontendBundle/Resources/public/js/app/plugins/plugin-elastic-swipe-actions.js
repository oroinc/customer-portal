define(function(require) {
    'use strict';

    const BasePlugin = require('oroui/js/app/plugins/base/plugin');
    const viewportManager = require('oroui/js/viewport-manager').default;
    const mediator = require('oroui/js/mediator');
    const _ = require('underscore');
    const $ = require('jquery');

    /**
     * Elastic swipe actions plugin for frontend grid
     *
     * @class
     * @augments BasePlugin
     * @exports ElasticSwipeActions
     */
    const ElasticSwipeActions = BasePlugin.extend({
        /**
         * Current swiped element container
         * @property {jQuery}
         */
        currentSwipedContainer: null,

        /**
         * Selector for find swiped container
         * @property {String}
         */
        containerSelector: '.grid-row',

        /**
         * Control point for moving to the end position
         * @property {Number}
         */
        breakPointPosition: 50,

        /**
         * Dynamic breakpoint factor
         *
         * @property {Number}
         */
        breakFactor: 0.3,

        /**
         * Limit of end point
         * @property {Number}
         */
        maxLimit: 125,

        /**
         * On done CSS classname
         * @property {String}
         */
        swipeDoneClassName: 'swipe-done',

        /**
         * On active CSS classname
         * @property {String}
         */
        swipeActionClassName: 'swipe-active',

        /**
         * Out of the limit
         * @property {Boolean}
         */
        elastic: false,

        /**
         * Save end point position
         * @property {Number}
         */
        storedPos: 0,

        /**
         * Dynamic size for container offset
         * @property {String}
         */
        sizerSelector: '.action-cell',

        /**
         * Viewport manager options
         * @property {Object}
         */
        viewport: 'tablet',

        /**
         * @property {Boolean}
         */
        enabled: false,

        events: {
            swipestart: '_onStart',
            swipemove: '_onMove',
            swipeend: '_onEnd'
        },

        /**
         * @Constructor
         */
        constructor: function ElasticSwipeActions(grid, options) {
            ElasticSwipeActions.__super__.constructor.call(this, grid, options);
        },
        /**
         * @Initialize
         *
         * @param {Object} grid
         * @param {Object} options
         * @returns {*}
         */
        initialize: function(grid, options) {
            _.extend(this, _.pick(options || {},
                [
                    'containerSelector', 'breakPointPosition', 'maxLimit',
                    'swipeDoneClassName', 'elastic', 'viewport', 'sizerSelector'
                ]
            ));

            mediator.on(`viewport:${this.viewport}`, this.onViewportChange, this);
            return ElasticSwipeActions.__super__.initialize.call(this, grid, options);
        },

        /**
         * Is applicable viewport
         */
        isApplicable: function() {
            return viewportManager.isApplicable(this.viewport);
        },

        /**
         * Enable swipe handler
         */
        enable: function() {
            if (this.enabled || !this.isApplicable(this.viewport)) {
                return;
            }

            this.delegateEvents();

            return ElasticSwipeActions.__super__.enable.call(this);
        },
        /**
         * Disable swipe handler
         */
        disable: function() {
            if (!this.enabled) {
                return;
            }

            this._revertState();
            this.undelegateEvents();

            delete this.currentSwipedContainer;
            delete this.storedPos;

            return ElasticSwipeActions.__super__.disable.call(this);
        },

        /**
         * Destroy swipe handler
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.disable();

            return ElasticSwipeActions.__super__.dispose.call(this);
        },

        /**
         * Listen responsive changes
         *
         * @param {MediaQueryListEvent} e
         */
        onViewportChange: function(e) {
            if (e.matches) {
                this.enable();
            } else {
                this.disable();
            }
        },

        /**
         * Apply dynamic offset for swiping container
         *
         * @param {jQuery} container
         * @private
         */
        _applyDynamicOffset: function(container) {
            const sizer = container.find(this.sizerSelector);

            if (!sizer.length) {
                return;
            }

            const size = container.find(this.sizerSelector).outerWidth();

            this.maxLimit = size;
            this.breakPointPosition = size * this.breakFactor;
            container.css({
                paddingRight: size,
                marginRight: -size
            });
        },

        /**
         * On start swipe action functionality
         *
         * @param {Object} data
         * @param {DOM.element} target
         * @private
         */
        _onStart: function({target}) {
            const container = $(target).closest(this.containerSelector);

            if (this.sizerSelector) {
                this._applyDynamicOffset(container);
            }

            if (
                this.currentSwipedContainer &&
                !$(this.currentSwipedContainer).is(container)
            ) {
                this._revertState();
            }

            this.currentSwipedContainer = container;
            this.currentSwipedContainer.css({
                transition: ''
            });

            if (this.currentSwipedContainer.hasClass(this.swipeDoneClassName)) {
                this.storedPos = parseInt(this.currentSwipedContainer.data('offset'));
            }

            this.currentSwipedContainer.addClass(this.swipeActionClassName);
        },

        /**
         * On move swipe action functionality
         *
         * @param {Object} data
         * @private
         */
        _onMove: function({detail}) {
            const xAxe = detail.x - this.storedPos;

            if (!this.elastic &&
                (
                    (detail.direction === 'left' && Math.abs(xAxe) > this.maxLimit) ||
                    (detail.direction === 'right' && xAxe > 0)
                )
            ) {
                return;
            }

            // this.currentSwipedContainer.data('offset', detail.x);
            this.currentSwipedContainer.css({
                transform: 'translateX(' + xAxe + 'px)'
            });
        },

        /**
         * On end of swipe action functionality
         *
         * @param {Object} data
         * @private
         */
        _onEnd: function({detail}) {
            let xAxe = detail.x - this.storedPos;
            if (detail.direction === 'right' && xAxe > 0) {
                xAxe = 0;
            }

            if (
                (detail.direction === 'left' && Math.abs(xAxe) < this.breakPointPosition) ||
                (detail.direction === 'right' && Math.abs(xAxe) < (this.maxLimit - this.breakPointPosition))
            ) {
                this._revertState();
                return;
            }

            if (Math.abs(xAxe) > this.breakPointPosition) {
                this.currentSwipedContainer.data('offset', this.maxLimit);
                this.currentSwipedContainer.css({
                    transform: 'translateX(-' + this.maxLimit + 'px)',
                    transition: 'all 200ms ease-out'
                });

                this.storedPos = 0;

                this.currentSwipedContainer.addClass(this.swipeDoneClassName);
            }

            this.currentSwipedContainer.removeClass('swipe-active');
        },

        /**
         * Reset container state
         *
         * @private
         */
        _revertState: function() {
            if (!this.enabled || !this.currentSwipedContainer) {
                return;
            }

            this.currentSwipedContainer.data('offset', 0);
            this.storedPos = 0;
            this.currentSwipedContainer.css({
                transform: 'translateX(0)',
                transition: 'all 200ms ease-out'
            });

            this.currentSwipedContainer.removeClass(this.swipeDoneClassName);
            this.currentSwipedContainer.removeClass(this.swipeActionClassName);
        }
    });

    return ElasticSwipeActions;
});
