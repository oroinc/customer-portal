import $ from 'jquery';
import _ from 'underscore';
import Select2AutocompleteComponent from 'oro/select2-autocomplete-component';

const Select2AutocompleteEnabledLocalizationComponent = Select2AutocompleteComponent.extend({
    /**
     * @property {Object}
     */
    options: {
        enabledLocalizationSelect: '[name$="[enabledLocalization]"]',
        websiteSelect: '[name$="[website]"]',
        datagridName: 'enabled-localizations-select-grid',
        delimiter: ';'
    },

    /**
     * @property {Object}
     */
    $enabledLocalizationSelect: null,

    /**
     * @property {Object}
     */
    $websiteSelect: null,

    /**
     * @inheritdoc
     */
    constructor: function Select2AutocompleteEnabledLocalizationComponent(options) {
        Select2AutocompleteEnabledLocalizationComponent.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.options = _.defaults(options || {}, this.options);

        Select2AutocompleteEnabledLocalizationComponent.__super__.initialize.call(this, options);

        this.$websiteSelect = $(this.options.websiteSelect);
        this.$enabledLocalizationSelect = $(this.options.enabledLocalizationSelect);
    },

    /**
     * @inheritdoc
     */
    makeQuery: function(query) {
        return [query, this.$websiteSelect.val()].join(this.options.delimiter);
    }
});

export default Select2AutocompleteEnabledLocalizationComponent;
