import $ from 'jquery';
import FullScreenPopupView from 'orofrontend/default/js/app/views/fullscreen-popup-view';
import SidePanelHeader from './side-panel-header';

const SidePanelView = FullScreenPopupView.extend({
    constructor: function SidePanelView(options) {
        options.headerView = SidePanelHeader;
        SidePanelView.__super__.constructor.call(this, options);
    },

    show() {
        SidePanelView.__super__.show.call(this);

        this.createBackdrop();
    },

    _onShow() {
        SidePanelView.__super__._onShow.call(this);
        this.$popup.addClass('show');
        document.body.classList.add('no-scroll-safe');
        document.activeElement.setAttribute('tabindex', 0);
    },

    getFocusTabbableElement() {
        return this.content.$el;
    },

    close() {
        if (!this.$popup) {
            return;
        }

        this.$popup.removeClass('show');
        this.removeBackdrop();
        document.body.classList.remove('no-scroll-safe');

        this.$popup.one('transitionend', () => {
            SidePanelView.__super__.close.call(this);
        });
    },

    createBackdrop() {
        if (!this.$backdrop) {
            this.$backdrop = $('<div class="fullscreen-popup__backdrop" />');
        }

        this.$popup.before(this.$backdrop);
        this.$backdrop.on(`click${this.eventNamespace()}`, this.close.bind(this));
        setTimeout(() => this.$backdrop.addClass('show'));
    },

    removeBackdrop() {
        if (this.$backdrop) {
            this.$backdrop.removeClass('show');
            this.$backdrop.off(this.eventNamespace());

            this.$backdrop.one('transitionend', () => {
                this.$backdrop.remove();
                delete this.$backdrop;
            });
        }
    }
});

export default SidePanelView;
