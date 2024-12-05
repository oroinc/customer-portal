import 'oroui/js/app/modules/input-widgets';
import InputWidgetManager from 'oroui/js/input-widget-manager';
import Select2InputWidget from 'oroui/js/app/views/input-widget/select2';
import FrontendNumberInputWidget from 'orofrontend/default/js/app/views/input-widget/number';
import FrontendPasswordInputWidget from 'orofrontend/default/js/app/views/input-widget/password';
import ResponsiveDropdownWidget from 'orofrontend/default/js/app/views/input-widget/responsive-dropdown';
import ResponsiveStyler from 'orofrontend/default/js/app/views/input-widget/responsive-styler';
import IncrementInputView from 'orofrontend/default/js/app/views/input-widget/increment-input';

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

InputWidgetManager.addWidget('password', {
    selector: 'input[type="password"], [data-input-widget="password"]',
    Widget: FrontendPasswordInputWidget
});

InputWidgetManager.addWidget('responsive-dropdown', {
    selector: '[data-responsive-dropdown]',
    Widget: ResponsiveDropdownWidget
});

InputWidgetManager.addWidget('responsive-styler', {
    selector: '[data-responsive-styler]',
    Widget: ResponsiveStyler
});

InputWidgetManager.addWidget('increment-input', {
    selector: '[data-increment-input]',
    Widget: IncrementInputView
});
