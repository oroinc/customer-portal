import mediator from 'oroui/js/mediator';
import BackdropView from 'oroui/js/app/views/backdrop-view';
import FullScreenPopupView from 'orofrontend/default/js/app/views/fullscreen-popup-view';
import SidePanelHeader from './side-panel-header';
import SidePanelFooterView from './side-panel-footer-view';

const SidePanelView = FullScreenPopupView.extend({
    toggleBtnActiveClassName: 'side-panel-menu-opened',

    constructor: function SidePanelView(options) {
        options.headerView = SidePanelHeader;

        SidePanelView.__super__.constructor.call(this, options);
    },

    show() {
        SidePanelView.__super__.show.call(this);

        this.subview('backdrop', new BackdropView({
            container: this.$popup.parent(),
            onClickCallback: () => {
                this.close();
            }
        }));

        this.subview('backdrop').show();
    },

    _onShow() {
        SidePanelView.__super__._onShow.call(this);
        this.$popup.addClass('show');
        document.body.classList.add('no-scroll-safe');
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
    },

    remove() {
        if (this.$backdrop) {
            this.$backdrop.remove();
            delete this.$backdrop;
        }

        document.body.classList.remove('no-scroll-safe');

        SidePanelView.__super__.remove.call(this);
    },

    close() {
        if (!this.$popup) {
            return;
        }

        this.$popup.removeClass('show');
        this.subview('backdrop').hide();
        this.subview('sidePanelFooterView').hide();
        document.body.classList.remove('no-scroll-safe');

        if (parseFloat(this.$popup.css('transition-duration')) === 0) {
            return SidePanelView.__super__.close.call(this);
        }
        this.$popup.one('transitionend', () => {
            SidePanelView.__super__.close.call(this);
        });
    }
});

export default SidePanelView;
