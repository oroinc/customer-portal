define(function(require) {
    'use strict';

    var StyleBookPlaygroundWidgets;
    var StyleBookPlayground = require('orofrontend/js/app/views/style-book-playground');

    StyleBookPlaygroundWidgets = StyleBookPlayground.extend({
        constructor: function StyleBookPlaygroundWidgets() {
            return StyleBookPlaygroundWidgets.__super__.constructor.apply(this, arguments);
        },


    });

    return StyleBookPlaygroundWidgets;
});
