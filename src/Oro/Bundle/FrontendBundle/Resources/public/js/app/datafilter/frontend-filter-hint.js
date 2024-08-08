define(function(require) {
    'use strict';

    const FilterHint = require('orofilter/js/filter-hint');

    const FrontendFilterHint = FilterHint.extend({
        constructor: function FrontendFilterHint(...args) {
            FrontendFilterHint.__super__.constructor.apply(this, args);
        },

        getHintValuesCount() {
            return 1;
        },

        toggleVisibility(visibility) {
            this.$el.toggleClass('filter-criteria-hint-item--hidden', !visibility);
        },

        isFitInContainer(untilElement) {
            if (!this.el.parentNode || !this.visible) {
                return true;
            }

            const {top, right} = this.el.getBoundingClientRect();
            const {top: topContainer, right: rightContainer} = this.el.parentNode.getBoundingClientRect();
            const paddingTop = parseInt(getComputedStyle(this.el.parentNode).paddingTop);
            let rightContainerOffset = 0;

            if (untilElement) {
                const {width} = untilElement.getBoundingClientRect();
                rightContainerOffset = width;
            }

            return topContainer + paddingTop === top && right < rightContainer - rightContainerOffset;
        },

        getChips() {
            return this.visible ? [this] : [];
        }
    });

    return FrontendFilterHint;
});
