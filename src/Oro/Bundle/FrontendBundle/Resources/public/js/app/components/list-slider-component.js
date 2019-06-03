define(function(require) {
    'use strict';

    var ContentSliderComponent;
    var EmbeddedListComponent = require('orofrontend/js/app/components/embedded-list-component');
    var tools = require('oroui/js/tools');
    var mediator = require('oroui/js/mediator');
    var $ = require('jquery');
    var _ = require('underscore');
    require('slick');

    ContentSliderComponent = EmbeddedListComponent.extend({
        /**
         * @property {Object}
         */
        options: _.extend({}, EmbeddedListComponent.prototype.options, {
            useTransform: false, // transform in slick-slider breaks dropdowns
            mobileEnabled: true,
            slidesToShow: 4,
            slidesToScroll: 1,
            autoplay: false,
            autoplaySpeed: 2000,
            arrows: !tools.isMobile(),
            dots: false,
            infinite: false,
            additionalClass: 'embedded-list__slider no-transform',
            embeddedArrowsClass: 'embedded-arrows',
            loadingClass: 'loading'
        }),

        /**
         * @property {Number}
         */
        previousSlide: 0,

        /**
         * @inheritDoc
         */
        constructor: function ContentSliderComponent() {
            ContentSliderComponent.__super__.constructor.apply(this, arguments);
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
            this.addEmbeddedArrowsClass(this.$el, this.options.arrows || false);

            $(this.$el).on('init', function(event, slick) {
                if (self.options.additionalClass) {
                    self.$el.addClass(self.options.additionalClass);
                }

                self.$el.removeClass(self.options.loadingClass);

                self.$initItems = slick.$slides.slice(
                    slick.options.initialSlide,
                    slick.options.slidesToShow * slick.options.slidesPerRow
                );
            });

            if (this.options.mobileEnabled) {
                this.$el.addClass(this.options.loadingClass);
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
                self.addEmbeddedArrowsClass(slick.$slider, slick.options.arrows || false);
            });

            this.previousSlide = this.$el.slick('slickCurrentSlide');
            this.$el.on('afterChange', this._slickAfterChange.bind(this));
        },

        /**
         * @param {jQuery.Event} event
         * @param {slick} slick
         * @param {Number} currentSlide
         * @private
         */
        _slickAfterChange: function(event, slick, currentSlide) {
            if (this.previousSlide === currentSlide || !slick.$slides.length) {
                return;
            }

            var firstSlide = $(this.$el).slick('slickCurrentSlide');
            if (currentSlide > this.previousSlide ) {
                firstSlide += (slick.options.slidesToShow * slick.options.slidesPerRow) - 1;
            }

            var $shownItems = slick.$slides.slice(
                firstSlide,
                firstSlide + (slick.options.slidesToScroll * slick.options.slidesPerRow)
            );

            this.previousSlide = currentSlide;

            this.trigger('oro:embedded-list:shown', $shownItems);
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

        addEmbeddedArrowsClass: function(slider, bool) {
            var self = this;

            slider.toggleClass(self.options.embeddedArrowsClass, bool);
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$el.off('init');
            this.$el.off('destroy');
            this.$el.off('breakpoint');
            this.$el.off('afterChange');

            EmbeddedListComponent.__super__.dispose.apply(this, arguments);
        }
    });

    return ContentSliderComponent;
});
