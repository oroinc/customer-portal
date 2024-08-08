import Toolbar from 'orodatagrid/js/datagrid/toolbar';
import FrontendPageSize from 'orofrontend/js/datagrid/frontend-page-size';
import FrontendPaginationView from 'orofrontend/js/datagrid/frontend-pagination-view';
import FrontendVisibleItemsCounter from 'orofrontend/js/datagrid/frontend-visible-items-counter';
import FrontendActionsPanel from 'orofrontend/js/datagrid/frontend-actions-panel';

const FrontendToolbar = Toolbar.extend({
    pagination: FrontendPaginationView,

    /** @property */
    itemsCounter: FrontendVisibleItemsCounter,

    /** @property */
    actionsPanel: FrontendActionsPanel,

    constructor: function(...args) {
        FrontendToolbar.__super__.constructor.apply(this, args);
    },

    preinitialize(options) {
        const position = options.pageSize?.position || {};

        Object.entries(position).forEach(spot => {
            const place = spot[0];
            const config = spot[1];
            if (place === options.position && config?.responsivePageSize) {
                this.pageSize = FrontendPageSize;
            }
        });
    }
});

export default FrontendToolbar;
