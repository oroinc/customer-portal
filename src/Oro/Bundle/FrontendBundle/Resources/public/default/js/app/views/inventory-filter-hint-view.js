import FilterHint from 'orofilter/js/filter-hint';
import template from 'tpl-loader!orofrontend/default/templates/inventory-filter-hint-view.html';

const InventoryFilterHintView = FilterHint.extend({
    template,

    constructor: function InventoryFilterHintView(...args) {
        InventoryFilterHintView.__super__.constructor.apply(this, args);
    },

    update(hint) {
        if (hint !== null) {
            hint = this.filter.label;
        }

        return InventoryFilterHintView.__super__.update.call(this, hint);
    }
});

export default InventoryFilterHintView;
