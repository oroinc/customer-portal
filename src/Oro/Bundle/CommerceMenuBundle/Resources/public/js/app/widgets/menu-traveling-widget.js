define(function(require) {
    'use strict';

    const AbstractWidget = require('oroui/js/widget/abstract-widget');
    const mediator = require('oroui/js/mediator');
    const _ = require('underscore');
    const $ = require('jquery');

    const MenuTravelingWidget = AbstractWidget.extend({
        /**
         * @property {Object}
         */
        options: {
            currentClass: 'current',
            sectionSelector: '.main-menu__item',
            parentContainerSelector: '.fullscreen-popup',
            triggerSelector: '[data-go-to]'
        },

        /** @property */
        nestingLevel: 0,

        /** @property */
        $travelingTrigger: null,

        /** @property */
        $relatedTrigger: null,

        /** @property */
        $relatedContainer: null,

        /** @property */
        consideringTopPosition: 40,

        /**
         * @inheritdoc
         */
        keepElement: true,

        /**
         * @inheritdoc
         */
        constructor: function MenuTravelingWidget(options) {
            MenuTravelingWidget.__super__.constructor.call(this, options);
        },

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
            this.$travelingTrigger.on('click.nsMenuTravelingWidget', _.bind(this.goToSection, this));
            mediator.on('layout:reposition', _.debounce(this.updateHeight, 50), this);
        },

        /**
         * @param {Object} event
         */
        goToSection: function(event) {
            const $trigger = $(event.currentTarget);
            const goTo = $trigger.data('go-to');

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

            this.$relatedContainer.one('transitionend webkitTransitionEnd',
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
            if (this.disposed) {
                return;
            }

            const $container = this.$el.parents(this.options.parentContainerSelector);
            let containerHeight = 0;

            if ($container.length) {
                containerHeight = $container.height();
            }

            if (this.consideringTopPosition > 0) {
                containerHeight -= this.consideringTopPosition;
            }

            if (containerHeight > 0 && this.$relatedContainer) {
                this.$relatedContainer.css({
                    height: containerHeight
                });
            }
        },

        /**
         * @inheritdoc
         */
        dispose: function(options) {
            if (this.disposed) {
                return;
            }

            mediator.off(null, null, this);

            this.$travelingTrigger
                .off('.nsMenuTravelingWidget')
                .removeClass('current')
                .next()
                .removeAttr('style');

            delete this.$travelingTrigger;
            delete this.$relatedTrigger;
            delete this.$relatedContainer;
            delete this.nestingLevel;
            delete this.consideringTopPosition;

            MenuTravelingWidget.__super__.dispose.call(this);
        }
    });

    return MenuTravelingWidget;
});
