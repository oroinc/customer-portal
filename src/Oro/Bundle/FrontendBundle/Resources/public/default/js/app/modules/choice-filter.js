define(function(require) {
    'use strict';

    var _ = require('underscore');
    var ChoiceFilter = require('oro/filter/choice-filter');

    _.extend(ChoiceFilter.prototype, {
        criteriaValueSelectors: {
            value: '[data-choice-filter-name]',
            type: '[data-choice-filter-type]'
        },
        events: {
            'keyup input': '_onReadCriteriaInputKey',
            'keydown [type="text"]': '_preventEnterProcessing',
            'click .filter-update': '_onClickUpdateCriteria',
            'click .filter-criteria .filter-criteria-hide': '_onClickCloseCriteria',
            'click .disable-filter': '_onClickDisableFilter',
            'click .choice-value': '_onClickChoiceValue',
            'change [data-choice-value-select]': '_onSelectChoiceValue'
        },
        /**
         * Set value for hidden field
         *
         * @param {Event} e
         * @protected
         */
        _onSelectChoiceValue: function(e) {
            var type = $(e.currentTarget).val();
            var $typeInput = this.$(this.criteriaValueSelectors.type);
            $typeInput.each(function() {
                var $input = $(this);

                if ($input.is(':not(select)')) {
                    $input.val(type);

                    return true;
                }

                if ($input.is(':has(option[value=' + type + '])')) {
                    $input.val(type);

                    return true;
                }
            });
            this.fixSelects();
            $typeInput.trigger('change');

            this._handleEmptyFilter(type);
            this.trigger('onClickChoiceValue', this);
            e.preventDefault();
        }
    });
});
