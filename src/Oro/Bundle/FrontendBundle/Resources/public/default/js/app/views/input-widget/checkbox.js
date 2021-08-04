define(function(require) {
    'use strict';

    const $ = require('jquery');
    const AbstractInputWidget = require('oroui/js/app/views/input-widget/abstract');
    const {ENTER} = require('oroui/js/tools/keyboard-key-codes').default;

    const CheckboxInputWidget = AbstractInputWidget.extend({
        checkedParentCssClass: 'checked',

        widgetFunction: function() {
            this.getContainer().on('keydown keypress', this._handleEnterPress.bind(this));
            this.$el.on('change', this._handleChange.bind(this));
        },

        _handleEnterPress: function(event) {
            if (event.keyCode === ENTER) {
                event.preventDefault();
                this.$el.trigger('click');
            }
        },

        _handleChange: function() {
            const $content = $('[data-checkbox-triggered-content]');
            if (this.$el.prop('checked')) {
                this._on();
                $content.show();
            } else {
                this._off();
                $content.hide();
            }
        },

        _on: function() {
            this.$el.prop('checked', true);
            const {checkedParentCssClass} = this;
            if (checkedParentCssClass) {
                this.$el.parent().addClass(checkedParentCssClass);
            }
        },

        _off: function() {
            this.$el.prop('checked', false);
            this.$el.parent().removeClass('checked');
            const {checkedParentCssClass} = this;
            if (checkedParentCssClass) {
                this.$el.parent().removeClass(checkedParentCssClass);
            }
        },

        findContainer: function() {
            return this.$el.closest('label');
        }
    });

    return CheckboxInputWidget;
});
