import AbstractInputWidgetView from 'oroui/js/app/views/input-widget/abstract';
import IncrementInputView from 'orofrontend/default/js/app/views/increment-input/increment-input-view';

const IncrementInput = AbstractInputWidgetView.extend({
    /**
     * @inheritdoc
     */
    constructor: function IncrementInput(options) {
        IncrementInput.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize(options) {
        IncrementInput.__super__.initialize.call(this, options);
        this.initIncrementInput();
    },

    initIncrementInput(options = {}) {
        this.inputView = new IncrementInputView({...options, el: this.el});
    },

    disposeIncrementInput() {
        if (this.inputView && !this.inputView.disposed) {
            this.inputView.dispose();
        }
    },

    refresh() {
        this.disposeIncrementInput();
        this.initIncrementInput();
        return IncrementInput.__super__.refresh.call(this);
    },

    /**
     * Find widget root element
     * @returns {jQuery.Element}
     */
    findContainer() {
        return this.$el;
    },

    /**
     * Nothing to do
     * @inheritdoc
     */
    widgetFunction() {
        return this;
    },

    /**
     * Destroy widget
     * @inheritdoc
     */
    dispose() {
        if (this.disposed) {
            return;
        }

        this.disposeIncrementInput();

        return IncrementInput.__super__.dispose.call(this);
    }
});

export default IncrementInput;
