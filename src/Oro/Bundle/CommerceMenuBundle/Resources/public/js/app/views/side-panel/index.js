import mediator from 'oroui/js/mediator';
import OverlayPopupView from 'orofrontend/default/js/app/views/overlay-popup-view';
import SidePanelHeader from './side-panel-header';
import SidePanelFooterView from './side-panel-footer-view';

const SidePanelView = OverlayPopupView.extend({
    toggleBtnActiveClassName: 'side-panel-menu-opened',

    constructor: function SidePanelView(options) {
        options.headerView = SidePanelHeader;

        SidePanelView.__super__.constructor.call(this, options);
    },

    getFocusTabbableElement() {
        return this.content.$el;
    },

    showSection(section) {
        const promise = SidePanelView.__super__.showSection.call(this, section);

        if (section === 'footer') {
            this.subview('sidePanelFooterView', new SidePanelFooterView({
                el: this[section].$el[0],
                $popup: this.$popup,
                ...this[section].options
            }));
        }

        mediator.trigger(`${this.popupName}:${section}:shown`);

        return promise;
    },

    closeSection(section) {
        SidePanelView.__super__.closeSection.call(this, section);

        mediator.trigger(`${this.popupName}:${section}:closed`);
    }
});

export default SidePanelView;
