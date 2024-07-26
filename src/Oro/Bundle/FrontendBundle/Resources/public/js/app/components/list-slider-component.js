define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const tools = require('oroui/js/tools');
    const EmbeddedListComponent = require('orofrontend/js/app/components/embedded-list-component');
    const mediator = require('oroui/js/mediator');
    const arrowTpl = require('tpl-loader!orofrontend/templates/slick-arrow-button.html');
    const rtl = _.isRTL();

    require('slick');

    const ContentSliderComponent = EmbeddedListComponent.extend({
        /**
         * @property {Object}
         */
        options: _.extend({}, EmbeddedListComponent.prototype.options, {
            // transform in slick-slider breaks dropdowns
            useTransform: false,
            use_slider_on_mobile: true,
            slidesToShow: 5,
            slidesToScroll: 1,
            autoplay: false,
            autoplaySpeed: 2000,
            arrows: !tools.isMobile(),
            dots: false,
            // Showing count of dots before and after an active one
            maxDotsToShow: 2,
            infinite: false,
            additionalClass: 'embedded-list__slider no-transform',
            prevArrow: arrowTpl({
                ariaLabel: __('Previous'),
                iconName: 'chevron-left',
                className: 'slick-prev'
            }),
            nextArrow: arrowTpl({
                ariaLabel: __('Next'),
                iconName: 'chevron-right',
                className: 'slick-next'
            }),
            embeddedArrowsClass: 'embedded-arrows',
            loadingClass: 'loading',
            itemLinkSelector: null,
            processClick: null,
            rtl: rtl,
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
            this.options = this._processOptions(options);
            this.$el = options._sourceElement;

            this.listenTo(mediator, 'layout:reposition', this.updatePosition);
            this.addEmbeddedArrowsClass(this.$el, this.options.arrows || false);

            $(this.$el).on('init' + this.eventNamespace(), (event, slick) => {
                if (this.options.additionalClass) {
                    this.$el.addClass(this.options.additionalClass);
                }

                this.$el.removeClass(this.options.loadingClass);

                this.$initItems = slick.$slides.slice(
                    slick.options.initialSlide,
                    slick.options.slidesToShow * slick.options.slidesPerRow
                );
                this.limitDots(slick);
            });

            this.$el.addClass(this.options.loadingClass);
            this.refreshPositions();
            $(this.$el).slick(this.options);

            if (this.options.relatedComponent) {
                this.onChange();
            }

            $(this.$el).on(`destroy${this.eventNamespace()}`, (event, slick) => {
                this.$el.removeClass(this.options.additionalClass);
            });

            $(this.$el).on(`breakpoint${this.eventNamespace()}`, (event, slick) => {
                this.addEmbeddedArrowsClass(slick.$slider, slick.options.arrows || false);
                this.limitDots(slick);
            });

            this.previousSlide = this.$el.slick('slickCurrentSlide');
            this.$el.on(`afterChange${this.eventNamespace()}`, this._slickAfterChange.bind(this));
            if (this.options.processClick) {
                this.$el.on(`click${this.eventNamespace()}`, this.options.processClick, this.toProcessClick.bind(this));
            }
        },

        /**
         * Procces options for each breakpoint
         * @param {Object} options
         * @returns {*}
         * @private
         */
        _processOptions(options) {
            options = _.defaults(options || {}, this.options);
            const {show_arrows_on_touchscreens: arrowsOnTouchscreens} = options;

            if (arrowsOnTouchscreens === void 0) {
                return options;
            }

            if (_.isTouchDevice()) {
                options.arrows = arrowsOnTouchscreens;

                if (Array.isArray(options.responsive)) {
                    options.responsive = options.responsive.map(breakpoint => {
                        if (breakpoint.settings === void 0) {
                            breakpoint.settings = {};
                        }

                        breakpoint.settings.arrows = arrowsOnTouchscreens;

                        return breakpoint;
                    });
                }
            }

            return options;
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
            this.limitDots(slick);
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

            if (event.target.tagName !== 'A' && $(event.target).parent('a').length === 0) {
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
         * @param {object} slick
         */
        limitDots(slick) {
            if (!this.options.maxDotsToShow) {
                return;
            }

            const {$dots} = slick;

            if (!$dots) {
                return;
            }

            const $activeDot = $dots.find('.slick-active');
            // an active dot and others around it
            const MAX_DOTS_TO_SHOW = 1 + this.options.maxDotsToShow * 2;
            const $dotsItems = $dots.children();
            const $dotsBefore = $dotsItems.slice(
                Math.max(0, $activeDot.index() - this.options.maxDotsToShow),
                $activeDot.index()
            );
            const $dotsAfter = $dotsItems.slice(
                $activeDot.index() + 1,
                $activeDot.index() + this.options.maxDotsToShow + 1
            );
            let $dotsToShow = $().add($dotsBefore).add($activeDot).add($dotsAfter);

            // Adding extra items is case there is not enough dots before
            if ($dotsToShow.length < MAX_DOTS_TO_SHOW) {
                $dotsToShow = $dotsToShow.add($dotsItems.slice(
                    $dotsToShow.last().index() + 1,
                    $dotsToShow.last().index() + MAX_DOTS_TO_SHOW - $dotsToShow.length + 1
                ));
            }

            // Adding extra items is case there is not enough dots after
            if ($dotsToShow.length < MAX_DOTS_TO_SHOW) {
                $dotsToShow = $dotsToShow.add($dotsItems.slice(
                    Math.max(0, $dotsToShow.first().index() - (MAX_DOTS_TO_SHOW - $dotsToShow.length)),
                    $dotsToShow.first().index()
                ));
            }

            const activeDotHeight = $activeDot.height();
            const activeDotWidth = $activeDot.width();

            $dotsItems.css({
                height: 0,
                width: 0,
                overflow: 'hidden'
            }).each((i, el) => $(el).attr('aria-hidden', true));
            $dotsToShow.css({
                height: activeDotHeight,
                width: activeDotWidth,
                overflow: ''
            }).each((i, el) => $(el).removeAttr('aria-hidden'));
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
