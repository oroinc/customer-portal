import BaseView from 'oroui/js/app/views/base/view';
import viewportManager from 'oroui/js/viewport-manager';
import templateDesktop from 'tpl-loader!orocommercemenu/templates/side-panel-header.html';
import template from 'tpl-loader!orofrontend/templates/fullscreen-popup/fullscreen-popup-header.html';

const SidePanelHeader = BaseView.extend({
    optionNames: ['templateData'],

    autoRender: true,

    template,

    templateDesktop,

    desktopMode: false,

    listen: {
        'viewport:mobile-big mediator': 'render'
    },

    constructor: function SidePanelHeader(...args) {
        SidePanelHeader.__super__.constructor.apply(this, args);
    },

    getTemplateFunction(key) {
        if (!this.isFullscreen()) {
            key = 'templateDesktop';
        }

        return SidePanelHeader.__super__.getTemplateFunction.call(this, key);
    },

    getTemplateData() {
        return this.templateData;
    },

    isFullscreen() {
        return viewportManager.isApplicable('mobile-big');
    }
});

export default SidePanelHeader;
