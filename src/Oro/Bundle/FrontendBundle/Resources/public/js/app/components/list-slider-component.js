define(function(require) {
    'use strict';

    const EmbeddedListComponent = require('orofrontend/js/app/components/embedded-list-component');
    const tools = require('oroui/js/tools');
    const mediator = require('oroui/js/mediator');
    const $ = require('jquery');
    const _ = require('underscore');
    require('slick');

    const ContentSliderComponent = EmbeddedListComponent.extend({
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
            loadingClass: 'loading',
            itemLinkSelector: null,
            processClick: null,
            rtl: _.isRTL(),
            // Enable or disable mouse dragging
            draggable: false
        }),

        /**
         * @property {Number}
         */
        previousSlide: 0,

        /**
         * @inheritdoc
         */
        constructor: function ContentSliderComponent(options) {
            ContentSliderComponent.__super__.constructor.call(this, options);
        },

        /**
         *
         * @param options
         */
        initialize: function(options) {
            const self = this;

            this.options = _.defaults(options || {}, this.options);
            this.$el = options._sourceElement;

            this.listenTo(mediator, 'layout:reposition', this.updatePosition);
            this.addEmbeddedArrowsClass(this.$el, this.options.arrows || false);

            $(this.$el).on('init' + this.eventNamespace(), function(event, slick) {
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

            $(this.$el).on(`destroy${this.eventNamespace()}`, function(event, slick) {
                self.$el.removeClass(self.options.additionalClass);
            });

            $(this.$el).on(`breakpoint${this.eventNamespace()}`, function(event, slick) {
                self.addEmbeddedArrowsClass(slick.$slider, slick.options.arrows || false);
            });

            this.previousSlide = this.$el.slick('slickCurrentSlide');
            this.$el.on(`afterChange${this.eventNamespace()}`, this._slickAfterChange.bind(this));
            if (this.options.processClick) {
                this.$el.on(`click${this.eventNamespace()}`, this.options.processClick, this.toProcessClick.bind(this));
            }
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

            let firstSlide = $(this.$el).slick('slickCurrentSlide');
            if (currentSlide > this.previousSlide ) {
                firstSlide += (slick.options.slidesToShow * slick.options.slidesPerRow) - 1;
            }

            const $shownItems = slick.$slides.slice(
                firstSlide,
                firstSlide + (slick.options.slidesToScroll * slick.options.slidesPerRow)
            );

            this.previousSlide = currentSlide;

            this.trigger('oro:embedded-list:shown', $shownItems);
        },

        refreshPositions: function() {
            const updatePosition = this.updatePosition.bind(this);
            $(this.$el).on('init', function(event, slick) {
                // This delay needed for waiting when slick initialized
                setTimeout(updatePosition, 100);
            });
        },

        onChange: function() {
            const self = this;

            const currentSlide = $(this.$el).slick('slickCurrentSlide');
            this.changeHandler(currentSlide, 'slider:activeImage');

            this.$el.on('beforeChange', function(event, slick, currentSlide, nextSlide) {
                self.changeHandler(nextSlide, 'slider:activeImage');
            });
        },

        changeHandler: function(nextSlide, eventName) {
            const activeImage = this.$el.find('.slick-slide[data-slick-index=' + nextSlide + '] img').get(0);
            this.$el.find('.slick-slide img')
                .data(eventName, activeImage)
                .trigger(eventName, activeImage);
        },

        updatePosition: function() {
            this.$el.slick('setPosition');
        },

        addEmbeddedArrowsClass: function(slider, bool) {
            const self = this;

            slider.toggleClass(self.options.embeddedArrowsClass, bool);
        },

        /**
         * @param {object} event
         */
        toProcessClick: function(event) {
            const selection = window.getSelection();

            // Allows to select text from the slide and prevents click on parent link element
            if (event.delegateTarget.contains(selection.anchorNode) && selection.toString().length) {
                event.preventDefault();
                return;
            }

            if (event.target.tagName !== 'A') {
                event.stopPropagation();

                const $link = $(event.currentTarget)
                    .closest(this.options.itemSelector).find(this.options.itemLinkSelector);

                if ($link.length) {
                    const mouseEvent = document.createEvent('MouseEvents');

                    mouseEvent.initEvent( 'click', true, true );
                    $link[0].dispatchEvent(mouseEvent);
                }
            }
        },

        /**
         * @returns {string}
         */
        eventNamespace: function() {
            return '.sliderEvents' + this.cid;
        },

        /**
         * @inheritdoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$el.off(this.eventNamespace());

            EmbeddedListComponent.__super__.dispose.call(this);
        }
    });

    return ContentSliderComponent;
});
