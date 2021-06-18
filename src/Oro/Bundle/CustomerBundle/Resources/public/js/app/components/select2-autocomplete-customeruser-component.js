define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const Select2AutocompleteComponent = require('oro/select2-autocomplete-component');

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

    return Select2AutocompleteCustomerUserComponent;
});
