define(function(require) {
    'use strict';

    const _ = require('underscore');
    const BaseChoiceFilter = require('oro/filter/choice-filter');

    const ChoiceFilter = BaseChoiceFilter.extend({
        /**
         * @inheritdoc
         */
        criteriaValueSelectors: _.defaults({
            type: '[data-choice-value-select]'
        }, BaseChoiceFilter.prototype.criteriaValueSelectors),

        events() {
            return {
                [`change ${this.criteriaValueSelectors.type}`]: '_onChangeChoiceValue'
            };
        },

        /**
         * @inheritdoc
         */
        constructor: function ChoiceFilter(options) {
            ChoiceFilter.__super__.constructor.call(this, options);
        },

        _renderCriteria: function() {
            ChoiceFilter.__super__._renderCriteria.call(this);

            this.$el.inputWidget('seekAndCreate');
        },

        /**
         * @inheritdoc
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

        getType() {
            return this.$(`${this.criteriaValueSelectors.type}:checked`).val();
        }
    });

    return ChoiceFilter;
});
