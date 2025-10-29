import BaseView from 'oroui/js/app/views/base/view';
import loadModules from 'oroui/js/app/services/load-modules';

const StyleBookZoomWidgetView = BaseView.extend({
    constructor: function StyleBookZoomWidgetView(options) {
        return StyleBookZoomWidgetView.__super__.constructor.call(this, options);
    },

    initialize: function(options) {
        StyleBookZoomWidgetView.__super__.initialize.call(this, options);
        loadModules('oroproduct/js/widget/zoom-widget')
            .then(() => this.$el.zoomWidget(options));
    },

    dispose: function() {
        if (this.disposed) {
            return;
        }

        this.$el.zoomWidget('destroy');

        StyleBookZoomWidgetView.__super__.dispose.call(this);
    }
});

export default StyleBookZoomWidgetView;
