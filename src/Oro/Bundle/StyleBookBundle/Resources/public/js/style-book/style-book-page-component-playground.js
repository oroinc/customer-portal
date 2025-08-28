import StyleBookPlayground from './style-book-playground';

const StyleBookPageComponentPlayground = StyleBookPlayground.extend({
    constructor: function StyleBookPageComponentPlayground(...args) {
        StyleBookPageComponentPlayground.__super__.constructor.apply(this, args);
    },

    initialize(options) {
        const {el, ...restOptions} = options;
        restOptions._sourceElement = el;

        StyleBookPageComponentPlayground.__super__.initialize.call(this, restOptions);
    },

    disposeView() {
        StyleBookPageComponentPlayground.__super__.disposeView.call(this);

        this.$(this.subviewContainer).empty();
    },

    createView(View) {
        StyleBookPageComponentPlayground.__super__.createView.call(this, View);

        this.$(this.subviewContainer).removeClass('hide');
    }
});

export default StyleBookPageComponentPlayground;
