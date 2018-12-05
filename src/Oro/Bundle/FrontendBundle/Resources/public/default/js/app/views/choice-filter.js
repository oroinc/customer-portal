define(function(require) {
    'use strict';

    var ChoiceFilter;
    var BaseChoiceFilter = require('oro/filter/choice-filter');

    ChoiceFilter = BaseChoiceFilter.extend({
        choiceSelectSelector: 'select[data-choice-value-select]',

        events: {
            'change select[data-choice-value-select]': '_onClickChoiceValue'
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
            this.$(this.choiceSelectSelector).each(function(i, elem) {
                var $select = this.$(elem);
                var name = $select.data('name') || 'type';
                if (oldValue[name] !== newValue[name]) {
                    $select.val(newValue[name]).trigger('change');
                }
            }.bind(this));

            ChoiceFilter.__super__._onValueUpdated.call(this, newValue, oldValue);
        }
    });

    return ChoiceFilter;
});
