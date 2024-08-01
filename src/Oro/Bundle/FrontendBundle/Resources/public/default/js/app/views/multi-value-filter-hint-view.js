import BaseView from 'oroui/js/app/views/base/view';
import FilterHint from 'orofilter/js/filter-hint';
import template from 'tpl-loader!orofrontend/default/templates/multi-value-filter-hint-view.html';

const MultiValueFilterHintView = BaseView.extend({
    optionNames: FilterHint.prototype.optionNames.concat(['filter', 'hintSeparator']),

    hintSeparator: ',',

    constructor: function MultiValueFilterHintView(...args) {
        MultiValueFilterHintView.__super__.constructor.apply(this, args);
    },

    update(hint) {
        const hints = hint === null ? [] : hint.split(this.hintSeparator).map(h => h.trim());

        this.filter.choices.forEach(({value: choice, label}) => {
            if (this.subview(`filter:sub-hint:${choice}`)) {
                this.subview(`filter:sub-hint:${choice}`).dispose();
            }

            if (hints.includes(label)) {
                const subHint = new FilterHint({
                    filter: this.filter
                });

                subHint.template = template;
                subHint.render();
                subHint.update(label);

                this.listenTo(subHint, 'reset', () => {
                    subHint.filter.setValue({
                        value: subHint.filter.getValue().value.filter(value => value !== choice)
                    });
                    subHint.dispose();
                });

                this.subview(`filter:sub-hint:${choice}`, subHint);
            }
        });
    }
});

export default MultiValueFilterHintView;
