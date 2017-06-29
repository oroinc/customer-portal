define(function(require) {
    'use strict';

    var ContentSliderComponent;
    var BaseComponent = require('oroui/js/app/components/base/component');
    var tools = require('oroui/js/tools');
    var mediator = require('oroui/js/mediator');
    var $ = require('jquery');
    var _ = require('underscore');
    require('slick');

    ContentSliderComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        options: {
            mobileEnabled: true,
            slidesToShow: 4,
            slidesToScroll: 1,
            autoplay: false,
            autoplaySpeed: 2000,
            arrows: !tools.isMobile(),
            dots: false,
            infinite: false,
            additionalClass: 'embedded-list-slider',
            embeddedArrowsClass: 'embedded-arrows'
        },

        /**
         *
         * @param options
         */
        initialize: function(options) {
            var self = this;

            this.options = _.defaults(options || {}, this.options);
            this.$el = options._sourceElement;

            this.listenTo(mediator, 'layout:reposition', this.updatePosition);
            self.addEmbeddedArrowsClass(this.$el, this.options);

            $(this.$el).on('init', function(event, slick) {
                if (self.$el.hasClass(self.options.additionalClass)) {
                    self.$el.addClass(self.options.additionalClass);
                }
            });

            if (this.options.mobileEnabled) {
                this.refreshPositions();
                $(this.$el).slick(this.options);
            }

            if (this.options.relatedComponent) {
                this.onChange();
            }

            $(this.$el).on('destroy', function(event, slick) {
                self.$el.removeClass(self.options.additionalClass);
            });

            $(this.$el).on('breakpoint', function(event, slick) {
                self.addEmbeddedArrowsClass(slick.$slider, slick.options);
            });
        },

        refreshPositions: function() {
            var updatePosition = _.bind(this.updatePosition, this);
            $(this.$el).on('init', function(event, slick) {
                // This delay needed for waiting when slick initialized
                setTimeout(updatePosition, 100);
            });
        },

        onChange: function() {
            var self = this;

            var currentSlide = $(this.$el).slick('slickCurrentSlide');
            this.changeHandler(currentSlide, 'slider:activeImage');

            this.$el.on('beforeChange', function(event, slick, currentSlide, nextSlide) {
                self.changeHandler(nextSlide, 'slider:activeImage');
            });
        },

        changeHandler: function(nextSlide, eventName) {
            var activeImage = this.$el.find('.slick-slide[data-slick-index=' + nextSlide + '] img').get(0);
            this.$el.find('.slick-slide img')
                .data(eventName, activeImage)
                .trigger(eventName, activeImage);
        },

        updatePosition: function() {
            this.$el.slick('setPosition');
        },

        addEmbeddedArrowsClass: function(slider, options) {
            var self = this;
            slider.toggleClass(self.options.embeddedArrowsClass, options.arrows);
        }
    });

    return ContentSliderComponent;
});
