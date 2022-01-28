define(function(require) {
    'use strict';

    const $ = require('jquery');
    const AbstractInputWidget = require('oroui/js/app/views/input-widget/abstract');

    const FromInputCustomDesign = AbstractInputWidget.extend({
        parentBaseCssClass: 'oro-form-control',

        parentCssClass: null,

        parentAttrs: null,

        iconBaseCssClass: 'oro-form-control__icon',

        iconCssClass: null,

        inputBaseCssClass: 'oro-form-control__input',

        inputCssClass: null,

        /**
         * @inheritdoc
         */
        initializeWidget(options) {
            FromInputCustomDesign.__super__.initializeWidget.call(this, options);
            this._applyDesignIfNeeded();
        },

        _applyDesignIfNeeded() {
            if (this.parentBaseCssClass && this.$el.parent(`.${this.parentBaseCssClass}`).length) {
                return this;
            }

            const $wrapEl = $(this.$el.parents('label').length ? '<span></span>' : '<label></label>');

            if (this.parentAttrs) {
                $wrapEl.attr(this.parentAttrs);
            }

            $wrapEl.addClass(this.collectClasses([this.parentBaseCssClass, this.parentCssClass]));
            this.$el.wrap($wrapEl).after($('<span></span>', {
                'class': this.collectClasses([this.iconBaseCssClass, this.iconCssClass]),
                'aria-hidden': true
            })).addClass(this.collectClasses([this.inputBaseCssClass, this.inputCssClass]))
                .data('designApplied', true);

            return this;
        },

        /**
         * @param {array} classes
         * @return {string|null}
         */
        collectClasses(classes) {
            if (!Array.isArray(classes)) {
                return null;
            }

            let result = '';
            for (const className of classes) {
                if (className) {
                    result = `${result} ${className}`;
                }
            }

            return result.length ? result.trim() : null;
        },

        removeDesign() {
            if (!this.$el.data('designApplied')) {
                return;
            }

            this.$el
                .unwrap()
                .removeClass(this.collectClasses([this.inputBaseCssClass, this.inputCssClass]))
                .removeData('designApplied')
                .next()
                .remove();
        },

        /**
         * @inheritdoc
         */
        disposeWidget() {
            this.removeDesign();

            FromInputCustomDesign.__super__.disposeWidget.call(this);
        }
    });

    return FromInputCustomDesign;
});
