define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    require('oroproduct/js/widget/zoom-widget');

    const StyleBookZoomWidgetView = BaseView.extend({
        constructor: function StyleBookZoomWidgetView(options) {
            return StyleBookZoomWidgetView.__super__.constructor.call(this, options);
        },

        initialize: function(options) {
            StyleBookZoomWidgetView.__super__.initialize.call(this, options);
            this.$el.zoomWidget(options);
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$el.zoomWidget('destroy');

            StyleBookZoomWidgetView.__super__.dispose.call(this);
        }
    });

    return StyleBookZoomWidgetView;
});
