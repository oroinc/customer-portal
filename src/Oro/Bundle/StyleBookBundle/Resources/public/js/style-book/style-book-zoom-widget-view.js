define(function(require) {
    'use strict';

    var StyleBookZoomWidgetView;
    var BaseView = require('oroui/js/app/views/base/view');
    require('oroproduct/js/widget/zoom-widget');

    StyleBookZoomWidgetView = BaseView.extend({
        constructor: function StyleBookZoomWidgetView() {
            return StyleBookZoomWidgetView.__super__.constructor.apply(this, arguments);
        },

        initialize: function(options) {
            StyleBookZoomWidgetView.__super__.initialize.apply(this, arguments);
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
