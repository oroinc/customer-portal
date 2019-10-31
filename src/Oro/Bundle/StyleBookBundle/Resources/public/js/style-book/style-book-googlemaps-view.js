define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const GooglemapsView = require('oroaddress/js/mapservice/googlemaps');

    const StyleBookGooglemapsView = BaseView.extend({
        constructor: function StyleBookGooglemapsView(options) {
            return StyleBookGooglemapsView.__super__.constructor.call(this, options);
        },

        initialize: function(options) {
            StyleBookGooglemapsView.__super__.initialize.call(this, options);

            this.subview('googleMapView', new GooglemapsView(options));
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }
            this.subview('googleMapView').$el.empty();
            StyleBookGooglemapsView.__super__.dispose.call(this);
        }
    });

    return StyleBookGooglemapsView;
});
