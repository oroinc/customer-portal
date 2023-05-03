define(function(require) {
    'use strict';

    const viewportManager = require('oroui/js/viewport-manager').default;
    const inputWidgetManager = require('oroui/js/input-widget-manager');
    const Select2InputWidget = require('oroui/js/app/views/input-widget/select2');
    const BaseView = require('oroui/js/app/views/base/view');
    const mediator = require('oroui/js/mediator');
    const _ = require('underscore');
    const $ = require('jquery');
    const scrollHelper = require('oroui/js/tools/scroll-helper');

    const StickyPanelView = BaseView.extend({
        /**
         * @inheritdoc
         */
        autoRender: false,

        /**
         * @property {Object}
         */
        options: {
            placeholderClass: 'moved-to-sticky',
            elementClass: 'in-sticky',
            scrollTimeout: 250,
            layoutTimeout: 60
        },

        /**
         * @property {jQuery.DOM}
         */
        $document: null,

        /**
         * @property {Object}
         */
        elements: null,

        /**
         * @property {Object}
         */
        $elements: null,

        /**
         * @property {Boolean}
         */
        scrollState: null,

        /**
         * @property {Object}
         */
        viewport: null,

        /**
         * @inheritdoc
         */
        constructor: function StickyPanelView(options) {
            this.onScroll = this.onScroll.bind(this);
            StickyPanelView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.options = _.extend({}, this.options, options || {});
            StickyPanelView.__super__.initialize.call(this, options);

            this.scrollState = {
                directionClass: '',
                position: 0
            };
            this.viewport = {
                top: 0,
                bottom: 0
            };

            this.listenToOnce(mediator, 'page:afterChange', this.render);
        },

        /**
         * @inheritdoc
         */
        setElement: function(element) {
            this.$document = $(document);
            return StickyPanelView.__super__.setElement.call(this, element);
        },

        /**
         * @inheritdoc
         * Init mediator listeners
         */
        delegateListeners: function() {
            StickyPanelView.__super__.delegateListeners.call(this);
            this.listenTo(mediator, 'layout:reposition', _.debounce(this.onScroll, this.options.layoutTimeout));
            this.listenTo(mediator, 'layout:content-relocated', _.debounce(this.onScroll, this.options.layoutTimeout));
            this.listenTo(mediator, 'page:afterChange', this.onAfterPageChange);
        },

        /**
         * @inheritdoc
         * Enable DOM document events
         */
        delegateEvents: function(events) {
            StickyPanelView.__super__.delegateEvents.call(this, events);

            if (scrollHelper.isPassiveEventSupported()) {
                document.addEventListener('scroll', this.onScroll, {
                    passive: true
                });
            } else {
                this.$document.on(
                    'scroll' + this.eventNamespace(),
                    _.throttle(this.onScroll, this.options.scrollTimeout)
                );
            }

            this.$document.on(
                'ajaxComplete' + this.eventNamespace(),
                this.reset.bind(this)
            );

            return this;
        },

        /**
         * @inheritdoc
         */
        undelegateEvents: function() {
            if (scrollHelper.isPassiveEventSupported()) {
                document.removeEventListener('scroll', this.onScroll);
            }

            if (this.$document) {
                this.$document.off(this.eventNamespace());
            }
            return StickyPanelView.__super__.undelegateEvents.call(this);
        },

        /**
         * @inheritdoc
         */
        render: function() {
            this.getElements();
            this.collectElements();
            this.applyAlwaysStickyElem();
            this.hasContent();
            this.rendered = true;

            return this;
        },

        /**
         * Update element collection after page change
         */
        onAfterPageChange: function() {
            const oldElements = this.elements;
            this.getElements();
            if (!_.isEqual(oldElements, this.elements)) {
                this.collectElements();
            }
        },

        /**
         * @inheritdoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.resetElement();
            this.undelegateEvents();

            _.each(['$document', 'elements', '$elements', 'scrollState', 'viewport'], function(key) {
                delete this[key];
            }, this);

            return StickyPanelView.__super__.dispose.call(this);
        },

        /**
         * Reset sticky element state
         */
        resetElement: function() {
            _.each(this.$elements, function($element) {
                if ($element.hasClass(this.options.elementClass)) {
                    this.toggleElementState($element, false);
                }
            }, this);

            this.elements = [];
            this.$elements = [];
        },

        /**
         * Reset sticky panel
         */
        reset: function() {
            if (!this.rendered) {
                return;
            }

            const oldElements = this.elements;
            this.getElements();

            if (// compare each element in lists, disregarding their order
                oldElements.length !== this.elements.length ||
                this.elements.some(el => oldElements.indexOf(el) === -1)
            ) {
                this.resetElement();
                this.render();

                this.onScroll();
            }
        },

        /**
         * Collect sticky elements
         */
        getElements: function() {
            const elementName = this.$el.data('sticky-name');
            const elSelector = elementName
                ? '[data-sticky-target="' + elementName + '"][data-sticky]' : '[data-sticky]';
            this.elements = $(elSelector).get();
        },

        /**
         * Collect sticky element with serialize
         */
        collectElements: function() {
            const $placeholder = this.$el.children();

            this.$elements = _.map(this.elements, function(element) {
                const $element = $(element);

                if ($element.data('sticky.initialized')) {
                    return $element;
                }

                const $elementPlaceholder = this.createPlaceholder()
                    .data('stickyElement', $element);
                const options = _.defaults($element.data('sticky') || {}, {
                    $elementPlaceholder: $elementPlaceholder,
                    viewport: 'all',
                    placeholderId: '',
                    toggleClass: '',
                    autoWidth: false,
                    isSticky: true,
                    affixed: false,
                    moveToPanel: true
                });
                options.$placeholder = options.placeholderId ? $('#' + options.placeholderId) : $placeholder;
                options.toggleClass += ' ' + this.options.elementClass;
                options.alwaysInSticky = false;
                options.currentState = false;

                $element.data('sticky', options);
                $element.data('sticky.initialized', true);

                return $element;
            }, this);

            if (this.$elements.length) {
                this.delegateEvents();
            } else {
                this.undelegateEvents();
            }
        },

        /**
         * Apply element if is always sticky
         */
        applyAlwaysStickyElem: function() {
            this.$el.find('[data-sticky]').each(function() {
                const $element = $(this);
                const sticky = $element.data('sticky');
                sticky.alwaysInSticky = true;
                $element.data('sticky', sticky);
            });
        },

        /**
         * Create placeholder element
         * @returns {jQuery}
         */
        createPlaceholder: function() {
            return $('<div/>').addClass(this.options.placeholderClass);
        },

        /**
         * On scroll page listener
         */
        onScroll: function() {
            if (this.disposed) {
                return;
            }

            this.updateScrollState();
            this.updateViewport();

            let contentChanged = false;

            _.each(this.$elements, function($element) {
                const newState = this.getNewElementState($element);
                if (newState !== null) {
                    contentChanged = true;
                    this.toggleElementState($element, newState);
                }
            }, this);

            if (contentChanged) {
                this.hasContent();
            }
        },

        /**
         * Apply has content styles for element sticky panel
         */
        hasContent: function() {
            this.$el.toggleClass('has-content', this.$el.find('.' + this.options.elementClass).length > 0);
        },

        /**
         * Generate sticky element new state
         * @param $element
         * @returns {*}
         */
        getNewElementState: function($element) {
            const options = $element.data('sticky');
            const isEmpty = $element.is(':empty');
            const onBottom = options.affixed ? this.onBottom(options) : false;
            const mediaTypeState = viewportManager.isApplicable(options.viewport);

            if (options.isSticky) {
                if (options.currentState) {
                    if (isEmpty && !onBottom) {
                        return false;
                    } else if (!options.alwaysInSticky &&
                        this.inViewport(options.$elementPlaceholder, options.moveToPanel) &&
                        !onBottom) {
                        return false;
                    } else if (!options.alwaysInSticky && onBottom) {
                        return false;
                    } else if (!options.moveToPanel && this.isFixedPositionChange($element)) {
                        return true;
                    }
                } else if (!isEmpty) {
                    if (options.alwaysInSticky ||
                        (mediaTypeState && !this.inViewport($element, null, options.affixed) && !onBottom)) {
                        return true;
                    }
                }
            }

            return null;
        },

        /**
         * Save and change new scroll page state
         */
        updateViewport: function() {
            this.viewport.top = $(window).scrollTop();
            this.viewport.bottom = this.viewport.top + $(window).height();
        },

        /**
         * Check if element overflow bottom border
         * @param options
         * @returns {boolean}
         */
        onBottom: function(options) {
            const documentHeight = this.$document.height();
            const footerHeight = $('[data-page-footer]').outerHeight();
            return (documentHeight - footerHeight) <= (this.scrollState.position + options.stickyHeight);
        },

        /**
         * Check if element into viewport
         * @param $element - The element to check if it is in the viewport
         * @param backMargin - Flag for enable/disable back margin correction
         * @param affixed - Flag if panel is stick all time
         * @returns {boolean}
         */
        inViewport: function($element, backMargin, affixed) {
            const elementTop = $element.offset().top;
            const elementHeight = $element.height();
            const backMarginValue = (backMargin ? elementHeight : 0);
            const elementBottom = elementTop + elementHeight;
            const stick = this._getStickyPanelSize();

            if ((affixed && elementBottom >= this.viewport.bottom) || this.viewport.top + stick.stickTop < elementTop) {
                return true;
            }

            // Sticky panel has just one item
            let topViewportThreshold = this.viewport.top + stick.stickTop;

            // In case of few items in sticky.
            // its height might be bigger then a position of appropriate element which should go back to the original position.
            // Therefore, necessary to skip a sticky panel height in calculation
            if (elementTop > this.viewport.top) {
                topViewportThreshold = this.viewport.top;
            }

            return (
                (elementBottom <= this.viewport.bottom - stick.stickBottom + backMarginValue) &&
                (elementTop + backMarginValue >= topViewportThreshold)
            );
        },

        /**
         * Check global scroll state
         */
        updateScrollState: function() {
            const position = this.$document.scrollTop();
            const directionClass = this.scrollState.position > position ? 'scroll-up' : 'scroll-down';

            if (this.scrollState.directionClass !== directionClass) {
                this.$el.removeClass(this.scrollState.directionClass)
                    .addClass(directionClass);

                this.scrollState.directionClass = directionClass;
            }

            this.scrollState.position = position;
        },

        /**
         * Change sticky element state and move
         * @param $element
         * @param state
         */
        toggleElementState: function($element, state) {
            const options = $element.data('sticky');

            if (!options.alwaysInSticky && options.$elementPlaceholder) {
                if (state) {
                    this.updateElementPlaceholder($element);
                    $element.before(options.$elementPlaceholder);
                    if (options.moveToPanel) {
                        options.$placeholder.append($element);
                    }
                } else {
                    if (options.moveToPanel) {
                        options.$elementPlaceholder.before($element);
                    }
                    options.$elementPlaceholder.remove();
                }
            }

            $element.toggleClass(options.toggleClass, state);
            options.currentState = state;
            if (options.affixed && state) {
                options.stickyHeight = this.$el.outerHeight();

                if (!options.moveToPanel) {
                    options.stickyHeight += $element.outerHeight(true);
                }
            }

            if (!options.moveToPanel) {
                this._setFixed($element, options.currentState);
            }

            $element.data('sticky', options);

            mediator.trigger('sticky-panel:toggle-state', {
                $element: $element,
                state: state,
                stickTo: this.$el.data('stick-to')
            });

            const select2Widgets = inputWidgetManager.findWidgetsInContainer($element, Select2InputWidget);

            _.invoke(select2Widgets, 'updateFixedMode', true);
        },

        /**
         * Update placeholder element params
         * @param $element
         */
        updateElementPlaceholder: function($element) {
            if (_.isUndefined($element.data('sticky').$elementPlaceholder)) {
                return;
            }

            $element.data('sticky').$elementPlaceholder.css({
                display: $element.css('display'),
                width: $element.data('sticky').autoWidth ? 'auto' : $element.outerWidth(),
                height: $element.outerHeight(),
                margin: this.getElementMargin($element[0]) || 0
            });
        },

        /**
         * Get margin from element
         * @param element
         * @returns {string}
         */
        getElementMargin: function(element) {
            const values = _.map(['top', 'right', 'bottom', 'left'], function(pos) {
                return window.getComputedStyle(element)['margin-' + pos];
            });
            return values.join(' ');
        },

        /**
         * Get sticky panel offset
         * @returns {{stickTop: number, stickBottom: number}}
         * @private
         */
        _getStickyPanelSize: function() {
            let stickTop = 0;
            let stickBottom = 0;

            $('[data-sticky-name]').each(function() {
                const $el = $(this);
                switch ($el.data('stick-to')) {
                    case 'top':
                        stickTop += $el.height();
                        break;
                    case 'bottom':
                        stickBottom += $el.height();
                        break;
                }
            });

            return {
                stickTop: stickTop,
                stickBottom: stickBottom
            };
        },
        /**
         * Apply sticky styles for element
         * @param state
         * @private
         */
        _setFixed: function($element, state) {
            const options = $element.data('sticky');
            $element.css(state ? {
                position: 'fixed',
                left: options.$elementPlaceholder.offset().left,
                top: this._getStickyPanelSize().stickTop,
                width: options.$elementPlaceholder.outerWidth()
            } : {
                position: '',
                left: '',
                top: '',
                width: ''
            });
        },

        /**
         * If horizontal position change at fixed blocks
         * @param $element
         * @returns {boolean}
         */
        isFixedPositionChange($element) {
            const options = $element.data('sticky');
            return $element.offset().left !== options.$elementPlaceholder.offset().left;
        }
    });

    return StickyPanelView;
});
