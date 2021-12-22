define(function(require) {
    'use strict';

    const $ = require('jquery');
    const AbstractInputWidget = require('oroui/js/app/views/input-widget/abstract');
    const {ENTER} = require('oroui/js/tools/keyboard-key-codes').default;

    const CheckboxInputWidget = AbstractInputWidget.extend({
        checkedParentCssClass: 'checked',

        widgetFunction() {
            this.getContainer().on('keydown keypress', this._handleEnterPress.bind(this));
            this.$el.on('change', this._handleChange.bind(this));
        },

        /**
         * @inheritdoc
         */
        initializeWidget(options) {
            CheckboxInputWidget.__super__.initializeWidget.call(this, options);
            this._applyDesignIfNeeded();
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
        },

        _applyDesignIfNeeded() {
            if (this.$el.parent('.custom-checkbox').length) {
                return this;
            }

            const wrapEL = this.$el.parents('label').length ? '<span></span>' : '<label></label>';
            this.$el.wrap($(wrapEL, {
                'class': 'custom-checkbox'
            })).after($('<span></span>', {
                'class': 'custom-checkbox__icon',
                'aria-hidden': true
            })).addClass('custom-checkbox__input')
                .data('designApplied', true);

            return this;
        },

        removeDesign() {
            if (!this.$el.data('designApplied')) {
                return;
            }

            this.$el
                .unwrap()
                .removeClass('custom-checkbox__input')
                .removeData('designApplied')
                .next()
                .remove();
        },

        /**
         * @inheritdoc
         */
        disposeWidget() {
            this.removeDesign();

            CheckboxInputWidget.__super__.disposeWidget.call(this);
        }
    });

    return CheckboxInputWidget;
});
