define(function(require) {
    'use strict';

    const StyleBookPlayground = require('orostylebook/js/style-book/style-book-playground');

    const StyleBookPlaygroundWidgets = StyleBookPlayground.extend({
        constructor: function StyleBookPlaygroundWidgets(options) {
            return StyleBookPlaygroundWidgets.__super__.constructor.call(this, options);
        }
    });

    return StyleBookPlaygroundWidgets;
});
