define(function(require) {
    'use strict';

    const $ = require('jquery');
    const FromInputCustomDesign = require('orofrontend/default/js/app/views/input-widget/from-input-custom-design');
    const {ENTER} = require('oroui/js/tools/keyboard-key-codes').default;

    const CheckboxInputWidget = FromInputCustomDesign.extend({
        checkedParentCssClass: 'checked',

        parentBaseCssClass: 'custom-checkbox',

        parentCssClass: null,

        parentAttrs: null,

        iconBaseCssClass: 'custom-checkbox__icon',

        iconCssClass: null,

        inputBaseCssClass: 'custom-checkbox__input',

        inputCssClass: null,

        widgetFunction() {
            this.getContainer().on('keydown keypress', this._handleEnterPress.bind(this));
            this.$el.on('change', this._handleChange.bind(this));
        },

        _handleEnterPress(event) {
            if (event.keyCode === ENTER) {
                event.preventDefault();
                this.$el.trigger('click');
            }
        },

        _handleChange() {
            const $content = $('[data-checkbox-triggered-content]');
            if (this.$el.prop('checked')) {
                this._on();
                $content.show();
            } else {
                this._off();
                $content.hide();
            }
        },

        _on() {
            this.$el.prop('checked', true);
            const {checkedParentCssClass} = this;
            if (checkedParentCssClass) {
                this.$el.parent().addClass(checkedParentCssClass);
            }
        },

        _off() {
            this.$el.prop('checked', false);
            this.$el.parent().removeClass('checked');
            const {checkedParentCssClass} = this;
            if (checkedParentCssClass) {
                this.$el.parent().removeClass(checkedParentCssClass);
            }
        },

        findContainer() {
            return this.$el.closest('label');
        }
    });

    return CheckboxInputWidget;
});
