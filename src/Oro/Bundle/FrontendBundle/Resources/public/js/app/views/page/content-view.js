define(function(require) {
    'use strict';

    var PageContentView;
    var $ = require('jquery');
    var _ = require('underscore');
    var tools = require('oroui/js/tools');
    var BaseContentView = require('oroui/js/app/views/page/content-view');

    PageContentView = BaseContentView.extend({
        /**
         * Sets focus on first form field in case active element
         * is not active on purpose (autofocus attribute)
         */
        initFocus: function() {
            console.log('initFocus - Frontend');
            if (tools.isMobile()) {
                return; // disable feature on mobile devices
            }

            var activeElement = document.activeElement;
            var delay = 200;
            var excludedSelectors = '.select2-offscreen, .select';
            var $form = this.$('form').filter(function() {
                    return $(this).hasClass('frontend_edit_form');
                }).first();

            $form.focusFirstInput(excludedSelectors);
            if (activeElement === document.activeElement) {
                _.delay(tools.focusScrollElement, delay);
            }
        }
    });

    return PageContentView;
});
