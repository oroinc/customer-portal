define(function(require) {
    'use strict';

    var MenuTravelingWidget;
    var AbstractWidget = require('oroui/js/widget/abstract-widget');
    var mediator = require('oroui/js/mediator');
    var _ = require('underscore');
    var $ = require('jquery');

    MenuTravelingWidget = AbstractWidget.extend({
        /**
         * @property {Object}
         */
        options: {
            currentClass: 'current',
            sectionSelector: '.main-menu__item',
            triggerSelector: '[data-go-to]',
            menuContainerSelector: '.main-menu-outer__container'
        },

        /** @property */
        nestingLevel: 0,

        /** @property */
        $relatedTrigger: $([]),

        /** @property */
        $relatedContainer: $([]),

        /** @property */
        consideringTopPosition: true,

        /**
         * @param {Object} options
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            this.$travelingTrigger = this.$(this.options.triggerSelector);

            this.hidePrevTrigger();
            this.bindEvents();
        },

        bindEvents: function() {
            this.$travelingTrigger.on('click', _.bind(this.goToSection, this));
            mediator.on('layout:reposition',  _.debounce(this.updateHeight, 50), this);
        },

        /**
         * @param {Object} event
         */
        goToSection: function(event) {
            var $trigger = $(event.currentTarget);
            var goTo = $trigger.data('go-to');

            if (goTo === 'next') {
                this.$relatedTrigger = $trigger;

                this.goToNextSection($trigger);
            }

            if (goTo === 'prev') {
                this.goToPrevSection(this.$relatedTrigger);

                // Find "next" button in a section one level higher
                this.$relatedTrigger = this.$relatedTrigger
                                           .parents(this.options.sectionSelector)
                                           .eq(this.nestingLevel)
                                           .find(this.options.triggerSelector);
            }

            this.$relatedContainer = this.$relatedTrigger.next();

            this.$relatedContainer.one('ransitionend webkitTransitionEnd oTransitionEnd',
                _.bind(this.hidePrevTrigger, this)
            );
            this.updateHeight(this.$relatedContainer);
        },

        goToNextSection: function($el) {
            if (this.lockTraveling($el)) {
                return;
            }

            this.nestingLevel += 1;

            $el.addClass(this.options.currentClass);
            $el.parent().addClass(this.options.currentClass);
        },

        /**
         * @param {jQuery} $el
         */
        goToPrevSection: function($el) {
            if (this.nestingLevel >= 0) {
                this.nestingLevel -= 1;
            }

            $el.removeClass(this.options.currentClass);
            $el.parent().removeClass(this.options.currentClass);
        },

        /**
         * @param {jQuery} $el
         */
        lockTraveling: function($el) {
            return this.nestingLevel > $el.parents(this.options.sectionSelector).length;
        },

        hidePrevTrigger: function() {
            this.$travelingTrigger
                .filter(function() {
                    return $(this).data('go-to') === 'prev';
                })
                .toggleClass('hidden', this.nestingLevel === 0);
        },

        /**
         * @param {jQuery} $el
         */
        updateHeight: function($el) {
            var $menuContainer = this.$(this.options.menuContainerSelector);
            var $popupParent = this.$el.closest('.fullscreen-popup');
            var menuHeight = 'auto';
            var containerHeight = 'auto';
            var setHeight = _.bind(function(menuHeight, containerHeight) {
                $menuContainer.css({
                    'height': menuHeight
                });

                this.$relatedContainer.css({
                    'height': containerHeight
                });
            }, this);

            if (this.nestingLevel === 0) {
                setHeight(menuHeight, containerHeight);
            } else {
                var $container = $el || $([]);
                menuHeight = $container.height();

                if ($popupParent.length) {
                    containerHeight = $popupParent.height();
                }

                if (this.consideringTopPosition) {
                    menuHeight += this.getRootTopPosition();
                    containerHeight -= this.getRootTopPosition();
                }

                setHeight(menuHeight, containerHeight);
            }
        },

        getRootTopPosition: function() {
            return 40;
        }
    });

    return MenuTravelingWidget;
});
