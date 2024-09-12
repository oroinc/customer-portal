define(function(require) {
    'use strict';

    const DateTimeFilter = require('oro/filter/datetime-filter');

    const FrontendDateTimeFilter = DateTimeFilter.extend({
        criteriaValueSelectors: {
            ...DateTimeFilter.prototype.criteriaValueSelectors,
            type: 'input[data-choice-value-select]'
        },

        events() {
            return {
                [`change ${this.criteriaValueSelectors.type}`]: 'onChangeFilterType'
            };
        },

        constructor: function FrontendDateTimeFilter(...args) {
            FrontendDateTimeFilter.__super__.constructor.apply(this, args);
        }
    });

    return FrontendDateTimeFilter;
});
