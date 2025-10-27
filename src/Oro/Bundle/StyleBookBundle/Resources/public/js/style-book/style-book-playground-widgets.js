import StyleBookPlayground from 'orostylebook/js/style-book/style-book-playground';

const StyleBookPlaygroundWidgets = StyleBookPlayground.extend({
    constructor: function StyleBookPlaygroundWidgets(options) {
        return StyleBookPlaygroundWidgets.__super__.constructor.call(this, options);
    }
});

export default StyleBookPlaygroundWidgets;
