import mediator from 'oroui/js/mediator';
import KEY_CODES from 'oroui/js/tools/keyboard-key-codes';
import manageFocus from 'oroui/js/tools/manage-focus';
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

    getContentTabbableElements() {
        return manageFocus.omitNotActiveRadioElements(
            this.content.$el.find(':visible:tabbable').toArray()
        );
    },

    getHeaderCloseFocusTarget(keyCode) {
        const tabbableElements = this.getContentTabbableElements();

        if (!tabbableElements.length) {
            return null;
        }

        if ([KEY_CODES.ARROW_DOWN, KEY_CODES.ARROW_RIGHT].includes(keyCode)) {
            return manageFocus.getFirstTabbable(tabbableElements);
        }

        if ([KEY_CODES.ARROW_UP, KEY_CODES.ARROW_LEFT].includes(keyCode)) {
            return manageFocus.getLastTabbable(tabbableElements);
        }

        return null;
    },

    onHeaderCloseKeyDown(event) {
        const focusTarget = this.getHeaderCloseFocusTarget(event.keyCode);

        if (!focusTarget) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        focusTarget.focus();
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

    _initPopupEvents() {
        SidePanelView.__super__._initPopupEvents.call(this);

        this.$popup.on(
            'keydown',
            '[data-role="header"] [data-role="close"]',
            this.onHeaderCloseKeyDown.bind(this)
        );
    },

    closeSection(section) {
        SidePanelView.__super__.closeSection.call(this, section);

        mediator.trigger(`${this.popupName}:${section}:closed`);
    }
});

export default SidePanelView;
