define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const FullscreenPopupView = require('orofrontend/default/js/app/views/fullscreen-popup-view');
    const _ = require('underscore');
    const $ = require('jquery');

    const FullscreenPopupDemoView = BaseView.extend({
        constructor: function FullscreenPopupDemoView(options) {
            FullscreenPopupDemoView.__super__.constructor.call(this, options);
        },

        initialize: function(options) {
            FullscreenPopupDemoView.__super__.initialize.call(this, options);
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
