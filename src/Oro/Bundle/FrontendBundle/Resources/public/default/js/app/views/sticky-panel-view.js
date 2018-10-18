define(function(require) {
    'use strict';

    var StickyPanelView;
    var viewportManager = require('oroui/js/viewport-manager');
    var BaseView = require('oroui/js/app/views/base/view');
    var mediator = require('oroui/js/mediator');
    var _ = require('underscore');
    var $ = require('jquery');

    StickyPanelView = BaseView.extend({
        /**
         * @inheritDoc
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
         * @inheritDoc
         */
        constructor: function StickyPanelView() {
            StickyPanelView.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.extend({}, this.options, options || {});
            StickyPanelView.__super__.initialize.apply(this, arguments);

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
         * @inheritDoc
         */
        setElement: function(element) {
            this.$document = $(document);
            return StickyPanelView.__super__.setElement.call(this, element);
        },

        /**
         * @inheritDoc
         * Init mediator listeners
         */
        delegateListeners: function() {
            StickyPanelView.__super__.delegateListeners.call(this);
            this.listenTo(mediator, 'layout:reposition', _.debounce(this.onScroll, this.options.layoutTimeout));
            this.listenTo(mediator, 'page:afterChange', this.onAfterPageChange);
        },

        /**
         * @inheritDoc
         * Enable DOM document events
         */
        delegateEvents: function() {
            StickyPanelView.__super__.delegateEvents.apply(this, arguments);

            this.$document.on(
                'scroll' + this.eventNamespace(),
                _.throttle(_.bind(this.onScroll, this), this.options.scrollTimeout)
            );

            this.$document.on(
                'ajaxComplete' + this.eventNamespace(),
                _.bind(this.reset, this)
            );

            return this;
        },

        /**
         * @inheritDoc
         */
        undelegateEvents: function() {
            if (this.$document) {
                this.$document.off(this.eventNamespace());
            }
            return StickyPanelView.__super__.undelegateEvents.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        render: function() {
            this.getElements();
            this.collectElements();
            this.applyAlwaysStickyElem();
            this.hasContent();
            return this;
        },

        /**
         * Update element collection after page change
         */
        onAfterPageChange: function() {
            var oldElements = this.elements;
            this.getElements();
            if (!_.isEqual(oldElements, this.elements)) {
                this.collectElements();
            }
        },

        /**
         * @inheritDoc
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

            return StickyPanelView.__super__.dispose.apply(this, arguments);
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
            var oldElements = this.elements;
            this.getElements();
            if (!_.isEqual(oldElements, this.elements)) {
                this.resetElement();
                this.render();

                this.onScroll();
            }
        },

        /**
         * Collect sticky elements
         */
        getElements: function() {
            var elementName = this.$el.data('sticky-name');
            var elSelector = elementName ? '[data-sticky-target="' + elementName + '"][data-sticky]' : '[data-sticky]';
            this.elements = $(elSelector).get();
        },

        /**
         * Collect sticky element with serialize
         */
        collectElements: function() {
            var $placeholder = this.$el.children();

            this.$elements = _.map(this.elements, function(element) {
                var $element = $(element);

                if ($element.data('sticky.initialized')) {
                    return $element;
                }

                var $elementPlaceholder = this.createPlaceholder()
                    .data('stickyElement', $element);
                var options = _.defaults($element.data('sticky') || {}, {
                    $elementPlaceholder: $elementPlaceholder,
                    viewport: {},
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
                var $element = $(this);
                var sticky = $element.data('sticky');
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

            var contentChanged = false;

            _.each(this.$elements, function($element) {
                var newState = this.getNewElementState($element);
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
            var options = $element.data('sticky');
            var isEmpty = $element.is(':empty');
            var onBottom = options.affixed ? this.onBottom(options) : false;
            var screenTypeState = viewportManager.isApplicable(options.viewport);

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
                    }
                } else if (!isEmpty) {
                    if (options.alwaysInSticky ||
                        (screenTypeState && !this.inViewport($element, null, options.affixed) && !onBottom)) {
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
            var documentHeight = this.$document.height();
            var footerHeight = $('[data-page-footer]').outerHeight();
            return (documentHeight - footerHeight) <= (this.scrollState.position + options.stickyHeight);
        },

        /**
         * Check if element into viewport
         * @param $element
         * @param backMargin
         * @param affixed
         * @returns {boolean}
         */
        inViewport: function($element, backMargin, affixed) {
            var elementTop = $element.offset().top;
            var elementHeight = $element.height();
            var backMarginValue = (backMargin ? elementHeight : 0);
            var elementBottom = elementTop + elementHeight;
            var stick = this._getStickyPanelSize();

            if ((affixed && elementBottom >= this.viewport.bottom) || this.viewport.top + stick.stickTop < elementTop) {
                return true;
            }

            return (
                (elementBottom <= this.viewport.bottom - stick.stickBottom + backMarginValue) &&
                (elementTop + backMarginValue >= this.viewport.top + stick.stickTop)
            );
        },

        /**
         * Check global scroll state
         */
        updateScrollState: function() {
            var position = this.$document.scrollTop();
            var directionClass = this.scrollState.position > position ? 'scroll-up' : 'scroll-down';

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
            var options = $element.data('sticky');

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

            mediator.trigger('sticky-panel:toggle-state', {$element: $element, state: state});
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
            var values = _.map(['top', 'right', 'bottom', 'left'], function(pos) {
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
            var stickTop = 0;
            var stickBottom = 0;

            $('[data-sticky-name]').each(function() {
                var $el = $(this);
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
            var options = $element.data('sticky');
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
        }
    });

    return StickyPanelView;
});
