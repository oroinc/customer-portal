import $ from 'jquery';
import tools from 'oroui/js/tools';
import BaseContentView from 'oroui/js/app/views/page/content-view';

const FrontendPageContentView = BaseContentView.extend({
    /**
     * @inheritdoc
     */
    constructor: function FrontendPageContentView(options) {
        FrontendPageContentView.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initFocus: function() {
        const activeElement = document.activeElement;
        if (tools.isMobile() || tools.isTouchDevice() || $(activeElement).is('[autofocus]')) {
            // disable feature on mobile devices
            return;
        }

        const $form = this.$('[data-focusable]:first');
        $form.focusFirstInput();
    }
});

export default FrontendPageContentView;
