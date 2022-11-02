import 'oroui/js/app/modules/input-widgets';
import InputWidgetManager from 'oroui/js/input-widget-manager';
import Select2InputWidget from 'oroui/js/app/views/input-widget/select2';
import FrontendNumberInputWidget from 'orofrontend/default/js/app/views/input-widget/number';

Select2InputWidget.prototype.closeOnOverlap = true;

InputWidgetManager.removeWidget('uniform-select');
InputWidgetManager.removeWidget('select2');
InputWidgetManager.removeWidget('number');

InputWidgetManager.addWidget('select2', {
    selector: 'select,input.select2, input.select-values-autocomplete',
    Widget: Select2InputWidget
});

InputWidgetManager.addWidget('number', {
    selector: 'input[type="number"], [data-input-widget="number"]',
    Widget: FrontendNumberInputWidget
});
