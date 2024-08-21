import VisibleItemsCounter from 'orodatagrid/js/datagrid/visible-items-counter';
import template from 'tpl-loader!orofrontend/templates/datagrid/frontend-visible-items-counter.html';

const FrontendVisibleItemsCounter = VisibleItemsCounter.extend({
    template,

    listen: {
        'backgrid:selected collection': 'render',
        'backgrid:selectAll collection': 'render',
        'backgrid:selectAllVisible collection': 'render',
        'backgrid:selectNone collection': 'render'
    },

    constructor: function FrontendVisibleItemsCounter(...args) {
        FrontendVisibleItemsCounter.__super__.constructor.apply(this, args);
    },

    getTemplateData(data) {
        return {
            ...FrontendVisibleItemsCounter.__super__.getTemplateData.call(this, data),
            selectedTotal: this.getSelectedTotal()
        };
    },

    /**
     * Get count of selected datagrid items
     *
     * @returns {number}
     */
    getSelectedTotal() {
        const selectedRows = {};
        this.collection.trigger('backgrid:getSelected', selectedRows);

        return selectedRows.inset
            ? selectedRows.selected.length
            : this.collection.length - selectedRows.selected.length;
    }
});

export default FrontendVisibleItemsCounter;
