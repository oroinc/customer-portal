define(function(require) {
    'use strict';

    const DictionaryFilter = require('oro/filter/dictionary-filter');

    const FrontendDictionaryFilter = DictionaryFilter.extend({
        criteriaValueSelectors: {
            ...DictionaryFilter.prototype.criteriaValueSelectors,
            type: 'input[data-choice-value-select]'
        },

        constructor: function FrontendDictionaryFilter(...args) {
            FrontendDictionaryFilter.__super__.constructor.apply(this, args);
        }
    });

    return FrontendDictionaryFilter;
});
