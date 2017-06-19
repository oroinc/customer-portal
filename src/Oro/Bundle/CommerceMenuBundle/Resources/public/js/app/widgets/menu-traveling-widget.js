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
        consideringTopPosition: 40,

        /**
         * @inheritDoc
         */
        keepElement: true,

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
            this.updateHeight();
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

        updateHeight: function() {
            var $popupParent = this.$el.closest('.fullscreen-popup');
            var containerHeight = 0;

            if ($popupParent.length) {
                containerHeight = $popupParent.height();
            }

            if (this.consideringTopPosition > 0) {
                containerHeight -= this.consideringTopPosition;
            }

            if (containerHeight > 0) {
                this.$relatedContainer.css({
                    'height': containerHeight
                });
            }
        },

        /**
         * @inheritDoc
         */
        dispose: function(options) {
            if (this.disposed) {
                return;
            }

            this.$travelingTrigger.off();
            mediator.off(null, null, this);

            delete this.nestingLevel;
            delete this.$relatedTrigger;
            delete this.$relatedContainer;
            delete this.consideringTopPosition;

            MenuTravelingWidget.__super__.dispose.apply(this, arguments);
        }
    });

    return MenuTravelingWidget;
});
