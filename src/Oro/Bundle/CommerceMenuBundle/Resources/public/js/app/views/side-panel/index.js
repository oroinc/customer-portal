import FullScreenPopupView from 'orofrontend/default/js/app/views/fullscreen-popup-view';
import SidePanelHeader from './side-panel-header';
import SidePanelFooterView from './side-panel-footer-view';
import SidePanelBackdropView from './side-panel-backdrop-view';

const SidePanelView = FullScreenPopupView.extend({
    constructor: function SidePanelView(options) {
        options.headerView = SidePanelHeader;

        SidePanelView.__super__.constructor.call(this, options);
    },

    show() {
        SidePanelView.__super__.show.call(this);

        this.subview('backdrop', new SidePanelBackdropView({
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

        return promise;
    },

    close() {
        if (!this.$popup) {
            return;
        }

        this.$popup.removeClass('show');
        this.subview('backdrop').hide();
        document.body.classList.remove('no-scroll-safe');

        this.$popup.one('transitionend', () => {
            SidePanelView.__super__.close.call(this);
        });
    }
});

export default SidePanelView;
