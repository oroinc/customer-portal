define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const PopupGalleryWidget = require('orofrontend/js/app/components/popup-gallery-widget');
    const _ = require('underscore');

    const StyleBookPopupGalleryWidget = BaseView.extend({
        constructor: function StyleBookPopupGalleryWidget(options) {
            return StyleBookPopupGalleryWidget.__super__.constructor.call(this, options);
        },

        initialize: function(options) {
            StyleBookPopupGalleryWidget.__super__.initialize.call(this, options);
            const galleryOptions = {
                galleryImages: [
                    {
                        thumb: '/bundles/orostylebook/images/promo-slider/promo-slider-small-1.jpg',
                        src: '/bundles/orostylebook/images/promo-slider/promo-slider-1.jpg',
                        alt: 'Slide 1'
                    },
                    {
                        thumb: '/bundles/orostylebook/images/promo-slider/promo-slider-small-2.jpg',
                        src: '/bundles/orostylebook/images/promo-slider/promo-slider-2.jpg',
                        alt: 'Slide 2'
                    },
                    {
                        thumb: '/bundles/orostylebook/images/promo-slider/promo-slider-small-3.jpg',
                        src: '/bundles/orostylebook/images/promo-slider/promo-slider-3.jpg',
                        alt: 'Slide 3'
                    }
                ]
            };
            const popupOptions = _.extend({}, options, galleryOptions);
            this.subview('popupGallery', new PopupGalleryWidget(popupOptions));
        }
    });

    return StyleBookPopupGalleryWidget;
});
