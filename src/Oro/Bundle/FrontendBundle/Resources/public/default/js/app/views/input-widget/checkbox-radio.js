define(function(require) {
    'use strict';

    const $ = require('jquery');
    const AbstractInputWidget = require('oroui/js/app/views/input-widget/abstract');

    const CheckboxRadioInputWidget = AbstractInputWidget.extend({
        widgetFunction() {},

        /**
         * @inheritdoc
         */
        initializeWidget(options) {
            CheckboxRadioInputWidget.__super__.initializeWidget.call(this, options);
            this._applyDesignIfNeeded();
        },

        findContainer() {
            return this.$el.closest('label');
        },

        _applyDesignIfNeeded() {
            if (this.$el.parent(`.custom-radio`).length) {
                return this;
            }

            const wrapEL = this.$el.parents('label').length ? '<span></span>' : '<label></label>';
            this.$el.wrap($(wrapEL, {
                'class': 'custom-radio'
            })).after($('<span></span>', {
                'class': 'custom-radio__text',
                'aria-hidden': true
            })).addClass('custom-radio__control')
                .data('designApplied', true);

            return this;
        },

        removeDesign() {
            if (!this.$el.data('designApplied')) {
                return;
            }

            this.$el
                .unwrap()
                .removeClass('custom-radio__control')
                .removeData('designApplied')
                .next()
                .remove();
        },

        /**
         * @inheritdoc
         */
        disposeWidget() {
            this.removeDesign();

            CheckboxRadioInputWidget.__super__.disposeWidget.call(this);
        }
    });

    return CheckboxRadioInputWidget;
});
