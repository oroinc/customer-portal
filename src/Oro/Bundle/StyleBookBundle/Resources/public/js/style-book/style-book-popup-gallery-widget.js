define(function(require) {
    'use strict';

    var StyleBookPopupGalleryWidget;
    var BaseView = require('oroui/js/app/views/base/view');
    var PopupGalleryWidget = require('orofrontend/js/app/components/popup-gallery-widget');
    var _ = require('underscore');

    StyleBookPopupGalleryWidget = BaseView.extend({
        constructor: function StyleBookPopupGalleryWidget() {
            return StyleBookPopupGalleryWidget.__super__.constructor.apply(this, arguments);
        },

        initialize: function(options) {
            StyleBookPopupGalleryWidget.__super__.initialize.apply(this, arguments);
            var galleryOptions = {
                galleryImages: [
                    {
                        thumb: '/bundles/orocatalog/images/promo-slider/promo-slider-small-1.jpg',
                        src: '/bundles/orocatalog/images/promo-slider/promo-slider-1.jpg',
                        alt: 'Slide 1'
                    },
                    {
                        thumb: '/bundles/orocatalog/images/promo-slider/promo-slider-small-2.jpg',
                        src: '/bundles/orocatalog/images/promo-slider/promo-slider-2.jpg',
                        alt: 'Slide 2'
                    },
                    {
                        thumb: '/bundles/orocatalog/images/promo-slider/promo-slider-small-3.jpg',
                        src: '/bundles/orocatalog/images/promo-slider/promo-slider-3.jpg',
                        alt: 'Slide 3'
                    }
                ]
            };
            var popupOptions = _.extend({}, options, galleryOptions);
            this.subview('popupGallery', new PopupGalleryWidget(popupOptions));
        }
    });

    return StyleBookPopupGalleryWidget;
});
