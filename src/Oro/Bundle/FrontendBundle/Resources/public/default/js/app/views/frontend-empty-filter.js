define(function(require) {
    'use strict';

    const EmptyFilter = require('oro/filter/empty-filter').default;

    const FrontendEmptyFilter = EmptyFilter.extend({
        constructor: function FrontendEmptyFilter(...args) {
            FrontendEmptyFilter.__super__.constructor.apply(this, args);
        },

        _onClickChoiceValueSetType(type) {
            const $typeInput = this.$(this.criteriaValueSelectors.type);

            if ($typeInput.is('[type="radio"]')) {
                type = [type];
            }

            FrontendEmptyFilter.__super__._onClickChoiceValueSetType.call(this, type);
        }
    });

    return FrontendEmptyFilter;
});
