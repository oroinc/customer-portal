define(function(require, exports, module) {
    'use strict';

    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const AbstractFilter = require('oro/filter/abstract-filter');

    let config = require('module-config').default(module.id);

    config = _.extend({
        animationDuration: 0,
        clearFilterSelector: '[data-role="clear-filter"]',
        filterEnableValueBadge: true,
        allowClearButtonInFilter: true
    }, config);

    const FrontendAbstractFilter = AbstractFilter.extend({
        /**
         * Duration of slide up/down filter criteria
         *
         * @property {Number}
         */
        animationDuration: config.animationDuration,

        /**
        * Reset filter selector
        *
        * @property {string}
        */
        clearFilterSelector: config.clearFilterSelector,

        /**
         * Enable showing badge with count of selected values
         *
         * @property {boolean}
         */
        filterEnableValueBadge: config.filterEnableValueBadge,

        /**
         * Enable reset button for particular filter
         * Allow reset filter separately
         *
         * @property {boolean}
         */
        allowClearButtonInFilter: config.allowClearButtonInFilter,

        /**
         * @inheritdoc
         */
        constructor: function FrontendAbstractFilter(options) {
            FrontendAbstractFilter.__super__.constructor.call(this, options);
        },

        /**
         * Set filter button class
         *
         * @param {Object} element
         * @param {Boolean} status
         * @protected
         */
        _setButtonPressed: function(element, status) {
            if (!this.animationDuration) {
                return FrontendAbstractFilter.__super__._setButtonPressed.call(this, element, status);
            }

            if (status) {
                element.slideDown(this.animationDuration, () => {
                    this._setButtonExpanded(true);
                    element.parent().addClass(this.buttonActiveClass);
                });
            } else {
                element.slideUp(this.animationDuration, () => {
                    this._setButtonExpanded(false);
                    element.parent().removeClass(this.buttonActiveClass);
                });
            }
        },

        /**
         * @return {Object}
         */
        getTemplateDataProps() {
            return {
                ...FrontendAbstractFilter.__super__.getTemplateDataProps.call(this),
                allowClearButtonInFilter: this.allowClearButtonInFilter,
                clearFilterButtonAriaLabel: __('oro.filter.clearFilterButton.aria_label', {
                    label: `${__('oro.filter.by')} ${this.label}`}
                )
            };
        },

        getHintChips() {
            return this.subview('hint').getChips();
        },

        _setInputValue: function(input, value) {
            const $input = this.$(input);

            switch ($input.attr('type')) {
                case 'radio':
                    $input.each((index, input) => {
                        const $input = this.$(input);
                        if ($input.attr('value') === value) {
                            $input.prop('checked', true).trigger('change');
                        } else {
                            $input.prop('checked', false);
                        }
                    });
                    break;
                default:
                    $input.val(value).trigger('change');
            }

            return this;
        }
    });

    return FrontendAbstractFilter;
});
