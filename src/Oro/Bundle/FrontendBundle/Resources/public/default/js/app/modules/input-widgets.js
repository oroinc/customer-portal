define(function(require) {
    'use strict';

    require('oroui/js/app/modules/input-widgets');
    const InputWidgetManager = require('oroui/js/input-widget-manager');
    const CheckboxInputWidget = require('orofrontend/default/js/app/views/input-widget/checkbox');
    const Select2InputWidget = require('oroui/js/app/views/input-widget/select2');
    const FrontendNumberInputWidget = require('orofrontend/default/js/app/views/input-widget/number');

    Select2InputWidget.prototype.closeOnOverlap = true;

    InputWidgetManager.removeWidget('uniform-select');
    InputWidgetManager.removeWidget('select2');
    InputWidgetManager.removeWidget('number');

    InputWidgetManager.addWidget('checkbox', {
        selector: 'input:checkbox:not(.invisible)',
        Widget: CheckboxInputWidget
    });

    InputWidgetManager.addWidget('select2', {
        selector: 'select,input.select2, input.select-values-autocomplete',
        Widget: Select2InputWidget
    });

    InputWidgetManager.addWidget('number', {
        selector: 'input[type="number"], [data-input-widget="number"]',
        Widget: FrontendNumberInputWidget
    });
});
