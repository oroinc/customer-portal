define(function(require) {
    'use strict';

    var FrontendNumberInputWidget;
    var NumberInputWidget = require('oroui/js/app/views/input-widget/number');

    FrontendNumberInputWidget = NumberInputWidget.extend({
        allowZero: false
    });

    return FrontendNumberInputWidget;
});
