import BaseView from 'oroui/js/app/views/base/view';
import FilterHint from 'orofilter/js/filter-hint';
import template from 'tpl-loader!orofrontend/default/templates/multi-value-filter-hint-view.html';

const MultiValueFilterHintView = BaseView.extend({
    autoRender: true,

    optionNames: FilterHint.prototype.optionNames.concat(['filter', 'hintSeparator']),

    hintSeparator: ',',

    constructor: function MultiValueFilterHintView(...args) {
        MultiValueFilterHintView.__super__.constructor.apply(this, args);
    },

    render() {
        this.filter.choices.forEach(({value: choice}) => {
            if (this.subview(`filter:sub-hint:${choice}`)) {
                this.subview(`filter:sub-hint:${choice}`).dispose();
            }

            const subHint = new FilterHint({
                filter: this.filter
            });

            subHint.template = template;
            subHint.render();

            this.listenTo(subHint, 'reset', () => {
                subHint.$el.tooltip('dispose');
                subHint.filter.setValue({
                    value: subHint.filter.getValue().value.filter(value => value !== choice)
                });
            });

            this.subview(`filter:sub-hint:${choice}`, subHint);
        });

        return this;
    },

    update(hint) {
        const hints = hint === null ? [] : hint.split(this.hintSeparator).map(h => h.trim());

        this.filter.choices.forEach(({value: choice, label}) => {
            if (!this.subview(`filter:sub-hint:${choice}`)) {
                return;
            }

            this.subview(`filter:sub-hint:${choice}`).update(hints.includes(label) ? label : null);
            this.subview(`filter:sub-hint:${choice}`).$el.tooltip({
                title: this.filter.label
            });
        });
    },

    getChips() {
        return this.filter.choices.map(({value: choice}) => {
            if (this.subview(`filter:sub-hint:${choice}`) && this.subview(`filter:sub-hint:${choice}`).visible) {
                return this.subview(`filter:sub-hint:${choice}`);
            }
        }).filter(Boolean);
    }
});

export default MultiValueFilterHintView;
