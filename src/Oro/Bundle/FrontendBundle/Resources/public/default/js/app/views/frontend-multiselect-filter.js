import MultiSelectFilter from 'oro/filter/multiselect-filter';
import MultiValueFilterHintView from './multi-value-filter-hint-view';

const FrontendMultiSelectFilter = MultiSelectFilter.extend({
    /**
     * @inheritdoc
     */
    populateDefault: false,

    HintView: MultiValueFilterHintView,

    /**
     * @inheritdoc
     */
    constructor: function FrontendMultiSelectFilter(options) {
        FrontendMultiSelectFilter.__super__.constructor.call(this, options);
    }
});

export default FrontendMultiSelectFilter;
