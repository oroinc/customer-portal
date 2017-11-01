define(function(require) {
    'use strict';

    var StickyPanelView;
    var viewportManager = require('oroui/js/viewport-manager');
    var BaseView = require('oroui/js/app/views/base/view');
    var mediator = require('oroui/js/mediator');
    var _ = require('underscore');
    var $ = require('jquery');

    StickyPanelView = BaseView.extend({
        autoRender: true,

        options: {
            placeholderClass: 'moved-to-sticky',
            elementClass: 'in-sticky',
            scrollTimeout: 60,
            layoutTimeout: 40
        },

        $document: null,

        elements: null,

        $elements: null,

        scrollState: null,

        viewport: null,

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
         */
        delegateEvents: function() {
            StickyPanelView.__super__.delegateEvents.apply(this, arguments);

            this.$document.on(
                'scroll' + this.eventNamespace(),
                _.throttle(_.bind(this.onScroll, this), this.options.scrollTimeout)
            );

            mediator.on('layout:reposition',  _.debounce(this.onScroll, this.options.layoutTimeout), this);
            mediator.on('page:afterChange', this.onAfterPageChange, this);

            return this;
        },

        /**
         * @inheritDoc
         */
        undelegateEvents: function() {
            this.$document.off(this.eventNamespace());
            mediator.off(null, null, this);

            return StickyPanelView.__super__.undelegateEvents.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        render: function() {
            this.applyAlwaysStickyElem();
            this.getElements();
            this.collectElements();
            return this;
        },

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

            _.each(this.$elements, function($element) {
                if ($element.hasClass(this.options.elementClass)) {
                    this.toggleElementState($element, false);
                }
            }, this);

            this.undelegateEvents();

            _.each(['$document', 'elements', '$elements', 'scrollState', 'viewport'], function(key) {
                delete this[key];
            }, this);

            return StickyPanelView.__super__.dispose.apply(this, arguments);
        },

        getElements: function() {
            var elementName = this.$el.data('sticky-name');
            var elSelector = elementName ? '[data-sticky-target="' + elementName + '"][data-sticky]' : '[data-sticky]';
            this.elements = $(elSelector).get();
        },

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
                    affixed: false
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

        applyAlwaysStickyElem: function() {
            this.$el.find('[data-sticky]').each(function() {
                var $element = $(this);
                var sticky = $element.data('sticky');
                sticky.alwaysInSticky = true;
                $element.data('sticky', sticky);
            });
        },

        createPlaceholder: function() {
            return $('<div/>').addClass(this.options.placeholderClass);
        },

        onScroll: function() {
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
                this.$el.toggleClass('has-content', this.$el.find('.' + this.options.elementClass).length > 0);
            }
        },

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
                               this.inViewport(options.$elementPlaceholder, true) && !onBottom) {
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

        updateViewport: function() {
            this.viewport.top = $(window).scrollTop();
            this.viewport.bottom = this.viewport.top + $(window).height();
        },

        onBottom: function(options) {
            var documentHeight = this.$document.height();
            var footerHeight = $('footer').outerHeight();
            return (documentHeight - footerHeight) <= (this.scrollState.position + options.stickyHeight);
        },

        inViewport: function($element, backMargin, affixed) {
            var elementTop = $element.offset().top;
            var elementHeight = $element.height();
            var backMarginValue = (backMargin ? elementHeight : 0);
            var elementBottom = elementTop + elementHeight;
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

            if ((affixed && elementBottom >= this.viewport.bottom) || this.scrollState.position < elementTop) {
                return true;
            }

            return (
                (elementBottom <= this.viewport.bottom - stickBottom + backMarginValue) &&
                (elementTop + backMarginValue >= this.viewport.top + stickTop)
            );
        },

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

        toggleElementState: function($element, state) {
            var options = $element.data('sticky');

            if (!options.alwaysInSticky) {
                if (state) {
                    this.updateElementPlaceholder($element);
                    $element.after(options.$elementPlaceholder);
                    options.$placeholder.append($element);
                } else {
                    options.$elementPlaceholder.before($element)
                        .remove();
                }
            }

            $element.toggleClass(options.toggleClass, state);
            options.currentState = state;
            if (options.affixed && state) {
                options.stickyHeight = this.$el.outerHeight();
            }
            $element.data('sticky', options);

            mediator.trigger('sticky-panel:toggle-state', {$element: $element, state: state});
        },

        updateElementPlaceholder: function($element) {
            $element.data('sticky').$elementPlaceholder.css({
                display: $element.css('display'),
                width: $element.data('sticky').autoWidth ? 'auto' : $element.outerWidth(),
                height: $element.outerHeight(),
                margin: this.getElementMargin($element[0]) || 0
            });
        },

        /**
         * Polyfill for Firefox which doesn't support jQuery '.css' method to get element margin
         */
        getElementMargin: function(element) {
            var positions = ['top', 'right', 'bottom', 'left'];
            var values = _.map(positions, function(pos) {
                return window.getComputedStyle(element)['margin-' + pos];
            });
            return values.join(' ');
        }
    });

    return StickyPanelView;
});
