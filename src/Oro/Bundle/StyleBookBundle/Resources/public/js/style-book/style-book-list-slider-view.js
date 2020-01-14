define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const ListSliderComponent = require('orofrontend/js/app/components/list-slider-component');

    const StyleBookListSliderView = BaseView.extend({
        constructor: function StyleBookListSliderView(options) {
            return StyleBookListSliderView.__super__.constructor.call(this, options);
        },

        initialize: function(options) {
            StyleBookListSliderView.__super__.initialize.call(this, options);

            this.subview('listSlider', new ListSliderComponent(options));
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$el.slick('unslick');
            this.$el.off();

            StyleBookListSliderView.__super__.dispose.call(this);
        }
    });

    return StyleBookListSliderView;
});
