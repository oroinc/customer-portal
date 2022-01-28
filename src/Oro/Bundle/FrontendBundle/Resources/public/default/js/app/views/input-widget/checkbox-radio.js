define(function(require) {
    'use strict';

    const FromInputCustomDesign = require('orofrontend/default/js/app/views/input-widget/from-input-custom-design');

    const CheckboxRadioInputWidget = FromInputCustomDesign.extend({
        parentBaseCssClass: 'custom-radio',

        parentCssClass: null,

        parentAttrs: null,

        iconBaseCssClass: 'custom-radio__text',

        iconCssClass: null,

        inputBaseCssClass: 'custom-radio__input',

        inputCssClass: null,

        widgetFunction() {},

        findContainer() {
            return this.$el.closest('label');
        }
    });

    return CheckboxRadioInputWidget;
});
