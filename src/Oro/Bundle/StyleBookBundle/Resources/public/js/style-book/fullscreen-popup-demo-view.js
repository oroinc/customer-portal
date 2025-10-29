import BaseView from 'oroui/js/app/views/base/view';
import FullscreenPopupView from 'orofrontend/default/js/app/views/fullscreen-popup-view';
import _ from 'underscore';
import $ from 'jquery';

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

export default FullscreenPopupDemoView;
