import BaseView from 'oroui/js/app/views/base/view';

const FilterBadgeHintView = BaseView.extend({
    optionNames: BaseView.prototype.optionNames.concat(['filter']),

    tagName: 'span',

    className: 'badge filter-badge-hint',

    filter: null,

    constructor: function FilterBadgeHintView(options) {
        FilterBadgeHintView.__super__.constructor.call(this, options);
    },

    initialize(options) {
        if (!options.filter) {
            throw new Error('Required option filter not found.');
        }

        this.listenTo(this.filter, {
            update: this.render,
            rendered: this.render
        });

        FilterBadgeHintView.__super__.initialize.call(this, options);
    },

    render() {
        FilterBadgeHintView.__super__.render.call(this);

        const {value} = this.filter.getValue();

        if (!this.filter.isEmpty()) {
            this.$el.removeClass('hidden');
            this.$el.text(Array.isArray(value) ? value.length : 1);
        } else {
            this.$el.addClass('hidden');
            this.$el.empty();
        }

        return this;
    }
});

export default FilterBadgeHintView;
