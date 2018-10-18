define(function(require) {
    'use strict';

    var PageContentView;
    var $ = require('jquery');
    var tools = require('oroui/js/tools');
    var BaseContentView = require('oroui/js/app/views/page/content-view');

    PageContentView = BaseContentView.extend({
        /**
         * @inheritDoc
         */
        constructor: function PageContentView() {
            PageContentView.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initFocus: function() {
            var activeElement = document.activeElement;
            if (tools.isMobile() || tools.isTouchDevice() || $(activeElement).is('[autofocus]')) {
                // disable feature on mobile devices
                return;
            }

            var $form = this.$('[data-focusable]:first');
            $form.focusFirstInput();
        }
    });

    return PageContentView;
});
