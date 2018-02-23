define(function(require) {
    'use strict';

    require('oroui/js/app/modules/input-widgets');
    var InputWidgetManager = require('oroui/js/input-widget-manager');
    var CheckboxInputWidget = require('orofrontend/default/js/app/views/input-widget/checkbox');
    var Select2InputWidget = require('oroui/js/app/views/input-widget/select2');
    var FrontendNumberInputWidget = require('orofrontend/default/js/app/views/input-widget/number');

    InputWidgetManager.removeWidget('uniform-select');
    InputWidgetManager.removeWidget('select2');
    InputWidgetManager.removeWidget('number');

    InputWidgetManager.addWidget('checkbox', {
        selector: 'input:checkbox',
        Widget: CheckboxInputWidget
    });

    InputWidgetManager.addWidget('select2', {
        selector: 'select,input.select2, input.select-values-autocomplete',
        Widget: Select2InputWidget
    });

    InputWidgetManager.addWidget('number', {
        selector: 'input[type="number"]',
        allowZero: false,
        Widget: FrontendNumberInputWidget
    });
});
