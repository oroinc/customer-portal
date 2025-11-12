import DictionaryFilter from 'oro/filter/dictionary-filter';

const FrontendDictionaryFilter = DictionaryFilter.extend({
    criteriaValueSelectors: {
        ...DictionaryFilter.prototype.criteriaValueSelectors,
        type: 'input[data-choice-value-select]'
    },

    constructor: function FrontendDictionaryFilter(...args) {
        FrontendDictionaryFilter.__super__.constructor.apply(this, args);
    },

    toggleVisibilityClearFilterButton(hidden) {
        const {type, value} = this._readDOMValue();
        hidden = type === this.emptyValue.type && ((Array.isArray(value) && !value.length) || value === null);

        FrontendDictionaryFilter.__super__.toggleVisibilityClearFilterButton.call(this, hidden);
    }
});

export default FrontendDictionaryFilter;
