import $ from 'jquery';
import _ from 'underscore';
import Select2AutocompleteComponent from 'oro/select2-autocomplete-component';

const Select2AutocompleteCustomerUserComponent = Select2AutocompleteComponent.extend({
    /**
     * @property {Object}
     */
    options: {
        customerSelect: '.customer-customer-select input[type="hidden"]',
        delimiter: ';'
    },

    /**
     * @property {Object}
     */
    $customerSelect: null,

    /**
     * @inheritdoc
     */
    constructor: function Select2AutocompleteCustomerUserComponent(options) {
        Select2AutocompleteCustomerUserComponent.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.options = _.defaults(options || {}, this.options);

        Select2AutocompleteCustomerUserComponent.__super__.initialize.call(this, options);

        this.$customerSelect = $(this.options.customerSelect);
    },

    /**
     * @inheritdoc
     */
    makeQuery: function(query) {
        return [query, this.$customerSelect.val()].join(this.options.delimiter);
    }
});

export default Select2AutocompleteCustomerUserComponent;
