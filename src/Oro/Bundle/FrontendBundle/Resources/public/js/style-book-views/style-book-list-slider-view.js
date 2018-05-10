define(function(require) {
    'use strict';

    var StyleBookListSliderView;
    var BaseView = require('oroui/js/app/views/base/view');
    var ListSliderComponent = require('orofrontend/js/app/components/list-slider-component');

    StyleBookListSliderView = BaseView.extend({
        constructor: function StyleBookListSliderView() {
            return StyleBookListSliderView.__super__.constructor.apply(this, arguments);
        },

        initialize: function(options) {
            StyleBookListSliderView.__super__.initialize.apply(this, arguments);

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
