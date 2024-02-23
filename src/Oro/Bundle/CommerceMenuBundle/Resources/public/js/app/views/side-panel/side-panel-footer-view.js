import BaseView from 'oroui/js/app/views/base/view';
import SidePanelBackdropView from './side-panel-backdrop-view';

const SidePanelFooterView = BaseView.extend({
    optionNames: BaseView.prototype.optionNames.concat(['$popup']),

    autoRender: true,

    isExpanded: false,

    $popup: null,

    events: {
        'click [data-name="side-panel-footer-expand"], [type="reset"]': 'expandCollapseFooter',
        'keydown': 'onKeydown'
    },

    constructor: function SidePanelFooterView(...args) {
        SidePanelFooterView.__super__.constructor.apply(this, args);
    },

    onKeydown(event) {
        if (event.keyCode === 27 && this.isExpanded) {
            event.stopPropagation();
            this.toggleExpand(false);
        }
    },

    expandCollapseFooter() {
        this.toggleExpand(!this.isExpanded);

        if (!this.isExpanded) {
            this.$('form').trigger('reset');
        }
    },

    toggleExpand(state) {
        this.subview('backdrop').toggle(state);
        this.$popup.toggleClass('side-menu-footer-expand', state);
        this.isExpanded = state;
    },

    hide() {
        this.toggleExpand(false);
        this.$('form').trigger('reset');
    },

    render() {
        SidePanelFooterView.__super__.render.call(this);

        this.subview('backdrop', new SidePanelBackdropView({
            container: this.$popup,
            onClickCallback: () => {
                this.toggleExpand(false);
            }
        }));

        this.toggleExpand(this.isExpanded);

        return this;
    },

    dispose() {
        if (this.disposed) {
            return;
        }

        this.$popup.removeClass('side-menu-footer-expand');

        SidePanelFooterView.__super__.dispose.call(this);
    }
});

export default SidePanelFooterView;
