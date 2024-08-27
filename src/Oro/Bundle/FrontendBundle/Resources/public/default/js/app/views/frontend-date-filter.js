define(function(require) {
    'use strict';

    const DateFilter = require('oro/filter/date-filter');

    const FrontendDateFilter = DateFilter.extend({
        criteriaValueSelectors: {
            ...DateFilter.prototype.criteriaValueSelectors,
            type: 'input[data-choice-value-select]'
        },

        events() {
            return {
                [`change ${this.criteriaValueSelectors.type}`]: 'onChangeFilterType'
            };
        },

        constructor: function FrontendDateFilter(...args) {
            FrontendDateFilter.__super__.constructor.apply(this, args);
        },

        onChangeFilterType() {
            this.changeFilterType(this.getType());
        },

        _writeDOMValue: function(value) {
            FrontendDateFilter.__super__._writeDOMValue.call(this, value);

            if (this.getType() !== value.type) {
                this._setInputValue(this.criteriaValueSelectors.type, value.type);
            }

            return this;
        },

        _readDOMValue: function() {
            return {
                ...FrontendDateFilter.__super__._readDOMValue.call(this),
                type: this._getInputValue(this.criteriaValueSelectors.type)
            };
        },

        _setInputValue(input, value) {
            const $input = this.$(input);
            const oldValue = $input.val();
            const {start, end} = this.criteriaValueSelectors.value;

            FrontendDateFilter.__super__._setInputValue.call(this, input, value);

            if (oldValue !== value && ($input.is(start) || $input.is(end))) {
                $input.trigger('change');
            }

            return this;
        }
    });

    return FrontendDateFilter;
});
