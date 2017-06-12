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
            this.elements = [];
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
            mediator.on('page:afterChange', this.render, this);

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
            this.collectElements();
            return this;
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            _.each(this.elements, function($element) {
                if ($element.hasClass(this.options.elementClass)) {
                    this.toggleElementState($element, false);
                }
            }, this);

            this.undelegateEvents();

            _.each(['$document', '$elements', 'scrollState', 'viewport'], function(key) {
                delete this[key];
            }, this);

            return StickyPanelView.__super__.dispose.apply(this, arguments);
        },

        collectElements: function() {
            var elementName = this.$el.data('sticky-name');
            var elSelector = elementName ? '[data-sticky-target="' + elementName + '"][data-sticky]' : '[data-sticky]';

            this.elements = $(elSelector).get();
            var $placeholder = this.$el.children();

            this.elements.forEach(function(element, i) {
                var $element = $(element);
                this.elements[i] = $element;

                var $elementPlaceholder = this.createPlaceholder()
                    .data('stickyElement', $element);

                var options = _.defaults($element.data('sticky') || {}, {
                    $elementPlaceholder: $elementPlaceholder,
                    viewport: {},
                    placeholderId: '',
                    toggleClass: '',
                    autoWidth: false,
                    isSticky: true
                });
                options.$placeholder = options.placeholderId ? $('#' + options.placeholderId) : $placeholder;
                options.toggleClass += ' ' + this.options.elementClass;
                options.alwaysInSticky = false;
                options.currentState = false;

                $element.data('sticky', options);
            }, this);

            this.$el.find('[data-sticky]').each(function() {
                var $element = $(this);
                var sticky = $element.data('sticky');
                sticky.alwaysInSticky = true;
                $element.data('sticky', sticky);
            });

            if (this.elements.length) {
                this.delegateEvents();
            } else {
                this.undelegateEvents();
            }
        },

        createPlaceholder: function() {
            return $('<div/>').addClass(this.options.placeholderClass);
        },

        onScroll: function() {
            this.updateScrollState();
            this.updateViewport();

            var contentChanged = false;

            this.elements.forEach(function($element) {
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

            var screenTypeState = viewportManager.isApplicable(options.viewport);

            if (options.isSticky) {
                if (options.currentState) {
                    if (isEmpty) {
                        return false;
                    } else if (!options.alwaysInSticky && this.inViewport(options.$elementPlaceholder, true)) {
                        return false;
                    }
                } else if (!isEmpty) {
                    if (options.alwaysInSticky || (screenTypeState && !this.inViewport($element))) {
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

        inViewport: function($element, backMargin) {
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
            $element.data('sticky', options);

            mediator.trigger('sticky-panel:toggle-state', {$element: $element, state: state});
        },

        updateElementPlaceholder: function($element) {
            $element.data('sticky').$elementPlaceholder.css({
                display: $element.css('display'),
                width: $element.data('sticky').autoWidth ? 'auto' : $element.outerWidth(),
                height: $element.outerHeight(),
                margin: $element.css('margin') || 0
            });
        }
    });

    return StickyPanelView;
});
