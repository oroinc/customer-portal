define(function(require) {
    'use strict';

    var ChoiceFilter;
    var BaseChoiceFilter = require('oro/filter/choice-filter');

    ChoiceFilter = BaseChoiceFilter.extend({
        choiceDropdownSelector: 'select[data-choice-value-select]',

        events: {
            'change select[data-choice-value-select]': '_onClickChoiceValue'
        },

        /**
         * @inheritDoc
         */
        constructor: function ChoiceFilter() {
            ChoiceFilter.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        _onClickChoiceValue: function(e) {
            var type = e.currentTarget.value;
            this._onClickChoiceValueSetType(type);
            this._updateValueField();
        },

        /**
         * @inheritDoc
         */
        _onValueUpdated: function(newValue, oldValue) {
            var $menu = this.$(this.choiceDropdownSelector);
            var name = $menu.data('name') || 'type';
            $menu.val(newValue[name]);

            this._updateCriteriaHint();
            this._triggerUpdate(newValue, oldValue);
        }
    });

    return ChoiceFilter;
});
