import BaseView from 'oroui/js/app/views/base/view';
import GooglemapsView from 'oroaddress/js/mapservice/googlemaps';

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

export default StyleBookGooglemapsView;
