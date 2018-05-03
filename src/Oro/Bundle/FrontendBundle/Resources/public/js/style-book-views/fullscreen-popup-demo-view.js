define(function(require) {
    'use strict';

    var FullscreenPopupDemoView;
    var BaseView = require('oroui/js/app/views/base/view');
    var FullscreenPopupView = require('orofrontend/blank/js/app/views/fullscreen-popup-view');
    var _ = require('underscore');

    FullscreenPopupDemoView = BaseView.extend({
        constructor: function FullscreenPopupDemoView() {
            FullscreenPopupDemoView.__super__.constructor.apply(this, arguments);
        },

        initialize: function(options) {
            FullscreenPopupDemoView.__super__.initialize.apply(this, arguments);
            this.subview('fullscreenView', new FullscreenPopupView(_.extend({}, options, {
                disposeOnClose: true,
                contentElement: $(_.template(options.contentTemplate)())
            })));
        },

        render: function() {
            this.subview('fullscreenView').show();
        }
    });

    return FullscreenPopupDemoView;
});
