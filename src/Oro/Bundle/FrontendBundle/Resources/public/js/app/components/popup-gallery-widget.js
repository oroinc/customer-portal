define(function(require) {
    'use strict';

    const AbstractWidget = require('oroui/js/widget/abstract-widget');
    const $ = require('jquery');
    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');
    const routing = require('routing');
    const error = require('oroui/js/error');
    const manageFocus = require('oroui/js/tools/manage-focus').default;
    require('slick');

    const BROWSER_SCROLL_SIZE = mediator.execute('layout:scrollbarWidth');

    const PopupGalleryWidget = AbstractWidget.extend({
        /**
         * @property {Object}
         */
        template: require('tpl-loader!orofrontend/templates/gallery-popup/gallery-popup.html'),

        /**
         * @property {Object}
         */
        options: {
            bindWithSlider: '.product-view-media-gallery',
            uniqueTriggerToOpenGallery: null,
            galleryImages: [],
            ajaxMode: false,
            ajaxRoute: 'oro_product_frontend_ajax_images_by_id',
            ajaxMethod: 'GET',
            galleryFilter: null,
            thumbnailsFilter: null,
            alt: '',
            use_thumb: false,
            initialSlide: false,
            imageOptions: {
                fade: true,
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: true,
                lazyLoad: 'progressive',
                asNavFor: null,
                adaptiveHeight: false,
                dots: true,
                infinite: true,
                rtl: _.isRTL()
            },
            navOptions: {
                slidesToShow: 7,
                slidesToScroll: 7,
                asNavFor: null,
                centerMode: true,
                focusOnSelect: true,
                lazyLoad: 'progressive',
                arrows: true,
                dots: false,
                variableWidth: true,
                infinite: true,
                rtl: _.isRTL()
            }
        },

        /**
         * @inheritdoc
         */
        constructor: function PopupGalleryWidget(options) {
            PopupGalleryWidget.__super__.constructor.call(this, options);
        },

        /**
         * @constructor
         * @param {Object} options
         */
        initialize: function(options) {
            this.options = {...this.options, ...options};

            this.$triggerGalleryOpen = this.$('[data-trigger-gallery-open]');

            if (this.options.uniqueTriggerToOpenGallery) {
                this.$triggerGalleryOpen = $(this.options.uniqueTriggerToOpenGallery);
            }

            this.$triggerGalleryOpen
                .on(`keydown${this.eventNamespace()}`, e => {
                    // Open gallery if SPACE or ENTER was pressed
                    if (e.keyCode === 32 || e.keyCode === 13) {
                        this.onOpenTriggerClick(e);
                    }
                })
                .on(`click${this.eventNamespace()}`, this.onOpenTriggerClick.bind(this));

            if (_.has(options, 'productModel')) {
                options.productModel.on('backgrid:canSelected', checked => {
                    this.toggleGalleryTrigger(checked);
                });

                const state = {};

                options.productModel.trigger('backgrid:getVisibleState', state);

                if (!_.isEmpty(state)) {
                    this.toggleGalleryTrigger(state.visible);
                }
            }

            if (this.options.ajaxMode) {
                this.options.galleryImages = [];
            }
        },

        bindEvents: function() {
            $(document).on(`keydown${this.eventNamespace()}`, e => {
                if (e.keyCode === 37) {
                    this.$gallery.slick('slickPrev');
                }

                if (e.keyCode === 39) {
                    this.$gallery.slick('slickNext');
                }

                // ESC
                if (e.keyCode === 27) {
                    this.$galleryWidgetClose.trigger('click');
                }
            });

            this.$galleryWidget
                .one(`transitionend${this.eventNamespace()}`,
                    () => manageFocus.focusTabbable(this.$galleryWidget)
                )
                .on(`keydown${this.eventNamespace()}`,
                    event => manageFocus.preventTabOutOfContainer(event, this.$galleryWidget)
                );

            this.listenTo(mediator, 'layout:reposition', this.onResize);
        },

        unbindEvents: function() {
            if (this.$galleryWidget) {
                this.$galleryWidget.off(`transitionend${this.eventNamespace()} keydown${this.eventNamespace()}`);
            }

            $(document).off(`keydown${this.eventNamespace()}`);

            this.stopListening(mediator, 'layout:reposition');
        },

        toggleGalleryTrigger: function(state) {
            this.$triggerGalleryOpen.toggleClass('hidden', state);
        },

        onOpen: function() {
            this.unbindEvents();
            this.render();
            $('html').css('margin-right', BROWSER_SCROLL_SIZE);
            $('body').addClass('gallery-popup-opened');
            this.$galleryWidget.addClass('popup-gallery-widget--opened');
            this.renderImages();
            if (this.useThumb()) {
                this.renderThumbnails();
            }
            this.setDependentSlide();

            if ($(document.activeElement).hasClass('focus-visible')) {
                this.beforeOpenFocusedElement = document.activeElement;
            }

            manageFocus.focusTabbable(this.$galleryWidget);
            this.$galleryWidget
                .one(`transitionend${this.eventNamespace()}`,
                    () => manageFocus.focusTabbable(this.$galleryWidget)
                )
                .on(`keydown${this.eventNamespace()}`,
                    event => manageFocus.preventTabOutOfContainer(event, this.$galleryWidget)
                );
            this.refreshPositions();
            this.bindEvents();
        },

        onOpenTriggerClick: function(e) {
            e.preventDefault();

            if (!this.options.ajaxMode || this.options.galleryImages.length) {
                this.onOpen();

                return;
            }

            const data = {
                id: this.options.id,
                filters: []
            };

            if (this.options.galleryFilter) {
                data.filters.push(this.options.galleryFilter);
            } else {
                error.showErrorInConsole('No have gallery filter!');
                return;
            }

            if (this.options.thumbnailsFilter) {
                data.filters.push(this.options.thumbnailsFilter);
            } else {
                this.options.use_thumb = false;
            }

            $.ajax({
                url: routing.generate(this.options.ajaxRoute, data),
                method: this.options.ajaxMethod,
                dataType: 'json',
                beforeSend: _.bind(function() {
                    mediator.execute('showLoading');
                }, this),
                success: data => {
                    _.each(data, function(item, key) {
                        const image = {
                            src: item[this.options.galleryFilter],
                            alt: this.options.alt
                        };
                        if (_.has(item, 'isInitial') && item['isInitial']) {
                            this.options.initialSlide = key;
                        }
                        if (this.useThumb()) {
                            image.thumb = item[this.options.thumbnailsFilter];
                        }
                        this.options.galleryImages.push(image);
                    }, this);
                    this.onOpen();
                },
                complete: () => {
                    mediator.execute('hideLoading');
                }
            });
        },

        onClose: function() {
            this.$galleryWidget.one('transitionend', () => {
                this.unbindEvents();
                this.setDependentSlide();
                this.$galleryWidget.detach();
                $('html').css('margin-right', '');
                $('body').removeClass('gallery-popup-opened');
                if (this.beforeOpenFocusedElement) {
                    this.beforeOpenFocusedElement.focus();

                    delete this.beforeOpenFocusedElement;
                }
            });

            this.$galleryWidget.removeClass('popup-gallery-widget--opened');
        },

        renderImages: function() {
            this.$gallery.not('.slick-initialized').slick(
                this.options.imageOptions
            );
        },

        renderThumbnails: function() {
            if (this.$thumbnails) {
                this.$thumbnails.not('.slick-initialized').slick(
                    this.options.navOptions
                );
                this.checkSlickNoSlide();
            }
        },

        setDependentSlide: function() {
            const dependentSlider = this.options.bindWithSlider;
            const dependentSliderItems = $(dependentSlider).find('.slick-slide');
            if (dependentSlider && dependentSliderItems.length) {
                const dependentSlide = $(dependentSlider).slick('slickCurrentSlide');
                this.$gallery.slick('slickGoTo', dependentSlide, true);
                if (this.useThumb()) {
                    this.$thumbnails.slick('slickGoTo', dependentSlide, true);
                }
            } else if (_.isNumber(this.options.initialSlide)) {
                this.$gallery.slick('slickGoTo', this.options.initialSlide, true);
                if (this.useThumb()) {
                    this.$thumbnails.slick('slickGoTo', this.options.initialSlide, true);
                }
            }
        },

        checkSlickNoSlide: function() {
            if (this.$thumbnails.length) {
                const getSlick = this.$thumbnails.slick('getSlick');
                if (this.$thumbnails && getSlick.slideCount <= getSlick.options.slidesToShow) {
                    this.$thumbnails.addClass('slick-no-slide');
                } else {
                    this.$thumbnails.removeClass('slick-no-slide');
                }
            }
        },

        onResize: function() {
            this.refreshPositions();
            _.delay(_.bind(this.refreshPositions, this), 500);
        },

        refreshPositions: function() {
            this.$gallery.slick('setPosition');
            if (this.useThumb()) {
                this.$thumbnails.slick('setPosition');
            }
        },

        useThumb: function() {
            return this.options.use_thumb;
        },

        render: function() {
            if (!this.$galleryWidget) {
                this.$galleryWidget = $(this.template({
                    images: this.options.galleryImages,
                    use_thumb: this.useThumb()
                }));

                this.$galleryWidgetClose = this.$galleryWidget.find('[data-trigger-gallery-close]');
                this.$galleryWidgetClose.on('click' + this.eventNamespace(), this.onClose.bind(this));

                this.$gallery = this.$galleryWidget.find('[data-gallery-images]');
                if (this.useThumb()) {
                    this.$thumbnails = this.$galleryWidget.find('[data-gallery-thumbnails]');
                }

                if (!this.options.navOptions.asNavFor) {
                    this.options.navOptions.asNavFor = '.' + this.$gallery.attr('class');
                }
                if (!this.options.imageOptions.asNavFor && this.useThumb()) {
                    this.options.imageOptions.asNavFor = '.' + this.$thumbnails.attr('class');
                }
            }
            $('body').append(this.$galleryWidget);
            // Force DOM redraw/refresh
            this.$galleryWidget.hide().show();
        },

        dispose: function() {
            this.unbindEvents();

            this.$triggerGalleryOpen.off(this.eventNamespace());

            if (this.$galleryWidgetClose) {
                this.$galleryWidgetClose.off(this.eventNamespace());
            }

            if (this.$galleryWidget && this.$galleryWidget.length) {
                this.$galleryWidget.remove();
            }

            delete this.$galleryWidget;
            delete this.beforeOpenFocusedElement;

            PopupGalleryWidget.__super__.dispose.call(this);
        }
    });

    return PopupGalleryWidget;
});
