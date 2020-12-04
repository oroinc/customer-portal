define(function(require) {
    'use strict';

    const _ = require('underscore');
    const BaseChoiceFilter = require('oro/filter/choice-filter');

    const ChoiceFilter = BaseChoiceFilter.extend({
        /**
         * @inheritDoc
         */
        criteriaValueSelectors: _.defaults({
            type: 'select[data-choice-value-select]'
        }, BaseChoiceFilter.prototype.criteriaValueSelectors),

        events: {
            'change select[data-choice-value-select]': '_onChangeChoiceValue'
        },

        /**
         * @inheritDoc
         */
        constructor: function ChoiceFilter(options) {
            ChoiceFilter.__super__.constructor.call(this, options);
        },

        _renderCriteria: function() {
            ChoiceFilter.__super__._renderCriteria.call(this);

            this.$el.inputWidget('seekAndCreate');
        },

        /**
         * @inheritDoc
         */
        _onChangeChoiceValue: function(e) {
            if (!this.changeChoiceValueHandling) {
                this.changeChoiceValueHandling = true;
                this._onClickChoiceValueSetType(e.currentTarget.value);
                this._updateValueField();
                delete this.changeChoiceValueHandling;
            }
            this._onValueChanged();
        },

        /**
         * @inheritDoc
         */
        _onValueUpdated: function(newValue, oldValue) {
            this.$(this.criteriaValueSelectors.type).each(function(i, elem) {
                const $select = this.$(elem);
                const name = $select.data('name') || 'type';
                if (oldValue[name] !== newValue[name]) {
                    $select.inputWidget('val', newValue[name]);
                    $select.trigger('change');
                }
            }.bind(this));

            ChoiceFilter.__super__._onValueUpdated.call(this, newValue, oldValue);
        }
    });

    return ChoiceFilter;
});
