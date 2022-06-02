define(function(require) {
    'use strict';

    const AbstractWidget = require('oroui/js/widget/abstract-widget');
    const $ = require('jquery');
    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');
    const routing = require('routing');
    const error = require('oroui/js/error');
    const manageFocus = require('oroui/js/tools/manage-focus').default;
    const Modal = require('oroui/js/modal');

    require('slick');

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
            },
            modalOptions: {
                // do not render (show) the "Ok" button
                allowCancel: false,
                // do not render (show) the "Cancel" button
                allowOk: false,
                className: 'modal oro-modal-normal popup-gallery-widget'
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
        initialize(options) {
            const {_initEvent: initEvent, ...restOptions} = options;
            this.options = {...this.options, ...restOptions};
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

            if (this.options.ajaxMode) {
                this.options.galleryImages = [];
            }

            if (
                initEvent && initEvent.type === 'click' && (
                    this.$triggerGalleryOpen.is(initEvent.target) ||
                    $.contains(this.$triggerGalleryOpen[0], initEvent.target)
                )
            ) {
                this.onOpenTriggerClick(initEvent);
            }
        },

        onOpen() {
            const modal = new Modal({
                ...this.options.modalOptions,
                content: this.template({
                    images: this.options.galleryImages,
                    use_thumb: this.useThumb()
                })
            });

            this.subview('modal', modal);
            this.listenTo(modal, {
                shown: this.onModalShown.bind(this, modal),
                close: this.onModalClose.bind(this, modal)
            });
            modal.open();
        },

        onOpenTriggerClick(e) {
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
                beforeSend: () => {
                    mediator.execute('showLoading');
                },
                success: data => {
                    _.each(data, function(item, key) {
                        const image = {
                            alt: this.options.alt
                        };
                        if (_.isArray(item[this.options.galleryFilter])) {
                            image.src = _.toArray(item[this.options.galleryFilter]).slice(-1)[0].srcset || '';
                            image.sources = _.toArray(item[this.options.galleryFilter]).slice(0, -1);
                        } else {
                            image.src = item[this.options.galleryFilter];
                        }
                        if (_.has(item, 'isInitial') && item['isInitial']) {
                            this.options.initialSlide = key;
                        }
                        if (this.useThumb()) {
                            if (_.isArray(item[this.options.thumbnailsFilter])) {
                                image.thumb = _.toArray(item[this.options.thumbnailsFilter]).slice(-1)[0].srcset || '';
                                image.thumbSources = _.toArray(item[this.options.thumbnailsFilter]).slice(0, -1);
                            } else {
                                image.thumb = item[this.options.thumbnailsFilter];
                            }
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

        onModalShown(modal) {
            const $gallery = modal.$('[data-gallery-images]');
            const $thumbnails = modal.$('[data-gallery-thumbnails]');

            if ($gallery.length === 0) {
                throw new Error('The template should contain an element with "data-gallery-images" attribute');
            }

            if ($(document.activeElement).hasClass('focus-visible')) {
                this.beforeOpenFocusedElement = document.activeElement;
            }

            if (!this.options.navOptions.asNavFor) {
                this.options.navOptions.asNavFor = `.${$gallery.attr('class')}`;
            }

            if (!this.options.imageOptions.asNavFor && this.useThumb()) {
                this.options.imageOptions.asNavFor = `.${$thumbnails.attr('class')}`;
            }

            // Initialize main slider
            $gallery.not('.slick-initialized').slick(this.options.imageOptions);

            let extraSlick = null;
            // Initialize extra slider if necessary
            if (this.useThumb() && $thumbnails.length) {
                $thumbnails.not('.slick-initialized').slick(this.options.navOptions);

                extraSlick = $thumbnails.slick('getSlick');

                $thumbnails
                    .toggleClass('slick-no-slide', extraSlick.slideCount <= extraSlick.options.slidesToShow);
            }

            const dependentSlider = this.options.bindWithSlider;
            let slideIndex = 0;

            if (dependentSlider && $(dependentSlider).find('.slick-slide').length) {
                slideIndex = $(dependentSlider).slick('slickCurrentSlide');
            } else if (typeof this.options.initialSlide === 'number') {
                slideIndex = this.options.initialSlide;
            }

            $gallery.slick('slickGoTo', slideIndex, true);

            if (extraSlick) {
                $thumbnails.slick('slickGoTo', slideIndex, true);
            }

            // Manually refresh positioning of slick
            const refreshPositions = () => {
                $gallery.slick('setPosition');

                if (extraSlick) {
                    $thumbnails.slick('setPosition');
                }
            };

            refreshPositions();
            this.listenTo(mediator, 'layout:reposition', () => {
                _.delay(() => refreshPositions(), 100);
            });
            $(document).on(`keydown${this.eventNamespace()}`, e => {
                if (e.keyCode === 37) {
                    $gallery.slick('slickPrev');
                } else if (e.keyCode === 39) {
                    $gallery.slick('slickNext');
                }
            });
            modal.$el.addClass('opened');
            manageFocus.focusTabbable($gallery);
        },

        onModalClose(modal) {
            if (this.beforeOpenFocusedElement) {
                this.beforeOpenFocusedElement.focus();

                delete this.beforeOpenFocusedElement;
            }

            this.stopListening(mediator);
            $(document).off(this.eventNamespace());
        },

        useThumb() {
            return this.options.use_thumb;
        },

        render() {
            return this;
        },

        dispose() {
            if (this.disposed) {
                return;
            }

            this.$triggerGalleryOpen.off(this.eventNamespace());
            delete this.beforeOpenFocusedElement;

            PopupGalleryWidget.__super__.dispose.call(this);
        }
    });

    return PopupGalleryWidget;
});
