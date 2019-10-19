define(function(require) {
    'use strict';

    var PopupGalleryWidget;
    var AbstractWidget = require('oroui/js/widget/abstract-widget');
    var $ = require('jquery');
    var _ = require('underscore');
    var mediator = require('oroui/js/mediator');
    var routing = require('routing');
    var error = require('oroui/js/error');
    require('slick');

    var BROWSER_SCROLL_SIZE = mediator.execute('layout:scrollbarWidth');

    PopupGalleryWidget = AbstractWidget.extend({
        /**
         * @property {Object}
         */
        template: require('tpl-loader!orofrontend/templates/gallery-popup/gallery-popup.html'),

        /**
         * @property {Object}
         */
        options: {
            bindWithSlider: '.product-view-media-gallery',
            galleryImages: [],
            ajaxMode: false,
            ajaxRoute: 'oro_product_frontend_ajax_images_by_id',
            ajaxMethod: 'GET',
            galleryFilter: null,
            thumbnailsFilter: null,
            alt: '',
            use_thumb: false,
            imageOptions: {
                fade: true,
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: true,
                lazyLoad: 'progressive',
                asNavFor: null,
                adaptiveHeight: false,
                dots: true,
                infinite: true
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
                infinite: true
            }
        },

        /**
         * @inheritDoc
         */
        constructor: function PopupGalleryWidget() {
            PopupGalleryWidget.__super__.constructor.apply(this, arguments);
        },

        /**
         * @constructor
         * @param {Object} options
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            this.$el = options._sourceElement;
            this.$galleryWidgetOpen = this.$el.find('[data-trigger-gallery-open]');

            if (_.has(options, 'productModel')) {
                var o = {};

                options.productModel.on('backgrid:canSelected', _.bind(function(checked) {
                    this.toggleGalleryTrigger(checked);
                }, this));

                options.productModel.trigger('backgrid:getVisibleState', o);

                if (!_.isEmpty(o)) {
                    this.toggleGalleryTrigger(o.visible);
                }
            }

            if (this.options.ajaxMode) {
                this.options.galleryImages = [];
            }

            this.bindEvents();
        },

        bindEvents: function() {
            if (this.options.ajaxMode) {
                this.$galleryWidgetOpen.on('click', _.bind(this.ajaxOpenDecorator, this));
            } else {
                this.$galleryWidgetOpen.on('click', _.bind(this.onOpen, this));
            }
        },

        unbindEvents: function() {
            this.$galleryWidgetOpen.off('click');
            if (this.$galleryWidgetClose) {
                this.$galleryWidgetClose.off('click');
            }
            mediator.off('layout:reposition', this.onResize, this);
        },

        toggleGalleryTrigger: function(state) {
            this.$galleryWidgetOpen.toggleClass('hidden', state);
        },

        onOpen: function(e) {
            e.preventDefault();
            var self = this;

            this.render();
            $('html').css('margin-right', BROWSER_SCROLL_SIZE);
            $('body').addClass('gallery-popup-opened');
            this.$galleryWidget.addClass('popup-gallery-widget--opened');
            this.renderImages();
            if (this.useThumb()) {
                this.renderThumbnails();
            }
            this.setDependentSlide();

            $(document).on('keydown.popup-gallery-widget', function(e) {
                if (e.keyCode === 37) {
                    self.$gallery.slick('slickPrev');
                }

                if (e.keyCode === 39) {
                    self.$gallery.slick('slickNext');
                }

                // ESC
                if (e.keyCode === 27) {
                    self.$galleryWidgetClose.trigger('click');
                }
            });
            this.refreshPositions();
            mediator.on('layout:reposition', this.onResize, this);
        },

        ajaxOpenDecorator: function(e) {
            e.preventDefault();

            if (this.options.galleryImages.length) {
                this.onOpen(e);
                return;
            }

            var data = {
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
                success: _.bind(function(data) {
                    _.each(data, function(item) {
                        var image = {
                            src: item[this.options.galleryFilter],
                            alt: this.options.alt
                        };
                        if (this.useThumb()) {
                            image.thumb = item[this.options.thumbnailsFilter];
                        }
                        this.options.galleryImages.push(image);
                    }, this);
                    this.onOpen(e);
                }, this),
                complete: _.bind(function() {
                    mediator.execute('hideLoading');
                }, this)
            });
        },

        onClose: function() {
            this.$galleryWidget.one('transitionend', _.bind(function() {
                this.setDependentSlide();
                this.$galleryWidget.detach();
                $('html').css('margin-right', '');
                $('body').removeClass('gallery-popup-opened');
            }, this));

            $(document).off('keydown.popup-gallery-widget');
            mediator.off('layout:reposition', this.onResize, this);

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
            var dependentSlider = this.options.bindWithSlider;
            var dependentSliderItems = $(dependentSlider).find('.slick-slide');
            if (dependentSlider && dependentSliderItems.length) {
                var dependentSlide = $(dependentSlider).slick('slickCurrentSlide');
                this.$gallery.slick('slickGoTo', dependentSlide, true);
                if (this.useThumb()) {
                    this.$thumbnails.slick('slickGoTo', dependentSlide, true);
                }
            }
        },

        checkSlickNoSlide: function() {
            if (this.$thumbnails.length) {
                var getSlick = this.$thumbnails.slick('getSlick');
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
                this.$galleryWidgetClose.on('click', _.bind(this.onClose, this));

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
            if (this.$galleryWidget && this.$galleryWidget.length) {
                this.$galleryWidget.remove();
            }

            delete this.$galleryWidget;
            delete this.$galleryWidgetOpen;

            PopupGalleryWidget.__super__.dispose.call(this);
        }
    });

    return PopupGalleryWidget;
});
