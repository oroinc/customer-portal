define(function(require) {
    'use strict';

    const NumberInputWidget = require('oroui/js/app/views/input-widget/number');

    const FrontendNumberInputWidget = NumberInputWidget.extend({
        allowZero: false
    });

    return FrontendNumberInputWidget;
});
