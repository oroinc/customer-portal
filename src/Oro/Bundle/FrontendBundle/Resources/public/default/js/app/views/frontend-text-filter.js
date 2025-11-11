define(function(require) {
    'use strict';

    const tools = require('oroui/js/tools');
    const TextFilter = require('oro/filter/text-filter').default;
    const FilterBadgeHintView = require('orofrontend/default/js/app/views/filter-badge-hint-view').default;

    const FrontendTextFilter = TextFilter.extend({
        events() {
            return {
                [`click ${this.clearFilterSelector}`]: '_onClickClearFilter'
            };
        },

        constructor: function FrontendTextFilter(...args) {
            FrontendTextFilter.__super__.constructor.apply(this, args);
        },

        rendered() {
            if (this.subview('filter:badge-hint')) {
                this.subview('filter:badge-hint').dispose();
            }

            if (this.filterEnableValueBadge) {
                this.subview('filter:badge-hint', new FilterBadgeHintView({
                    filter: this,
                    container: this.$('.filter-criteria-selector')
                }));
            }

            return FrontendTextFilter.__super__.rendered.call(this);
        },

        _showCriteria() {
            FrontendTextFilter.__super__._showCriteria.call(this);
            this.toggleVisibilityClearFilterButton();
        },

        _onClickClearFilter() {
            this.reset();
            this.toggleVisibilityClearFilterButton();
        },

        _onValueChanged() {
            FrontendTextFilter.__super__._onValueChanged.call(this);
            this.toggleVisibilityClearFilterButton();
        },

        toggleVisibilityClearFilterButton(hidden) {
            if (hidden === void 0) {
                hidden = tools.isEqualsLoosely(this._readDOMValue(), this.emptyValue);
            }

            const clearFilterButton = this.$(this.clearFilterSelector);
            clearFilterButton.toggleClass('hidden', hidden);
        }
    });

    return FrontendTextFilter;
});
