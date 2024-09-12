import BackdropView from 'oroui/js/app/views/backdrop-view';
import FullScreenPopupView from 'orofrontend/default/js/app/views/fullscreen-popup-view';

const OverlayPopupView = FullScreenPopupView.extend({
    disableBackDrop: false,

    constructor: function OverlayPopupView(options) {
        OverlayPopupView.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        OverlayPopupView.__super__.initialize.call(this, options);

        this.disableBackDrop = options.disableBackDrop || false;
    },

    show() {
        OverlayPopupView.__super__.show.call(this);

        if (!this.disableBackDrop) {
            this.subview('backdrop', new BackdropView({
                container: this.$popup.parent(),
                onClickCallback: () => {
                    this.close();
                }
            }));

            this.subview('backdrop').show();
        }
    },

    _onShow() {
        OverlayPopupView.__super__._onShow.call(this);
        this.$popup.addClass('show');
    },

    remove() {
        if (this.$backdrop) {
            this.$backdrop.remove();
            delete this.$backdrop;
        }

        OverlayPopupView.__super__.remove.call(this);
    },

    close() {
        if (!this.$popup) {
            return;
        }

        this.$popup.removeClass('show');
        if (!this.disableBackDrop) {
            this.subview('backdrop').hide();
        }

        if (parseFloat(this.$popup.css('transition-duration')) === 0) {
            return OverlayPopupView.__super__.close.call(this);
        }
        this.$popup.one('transitionend', () => {
            OverlayPopupView.__super__.close.call(this);
        });
    }
});

export default OverlayPopupView;
