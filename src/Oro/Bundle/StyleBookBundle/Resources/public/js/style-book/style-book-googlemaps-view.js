define(function(require) {
    'use strict';

    var StyleBookGooglemapsView;
    var BaseView = require('oroui/js/app/views/base/view');
    var GooglemapsView = require('oroaddress/js/mapservice/googlemaps');

    StyleBookGooglemapsView = BaseView.extend({
        constructor: function StyleBookGooglemapsView() {
            return StyleBookGooglemapsView.__super__.constructor.apply(this, arguments);
        },

        initialize: function(options) {
            StyleBookGooglemapsView.__super__.initialize.apply(this, arguments);

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
