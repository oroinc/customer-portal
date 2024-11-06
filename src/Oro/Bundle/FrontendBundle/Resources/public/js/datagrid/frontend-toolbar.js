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

    /**
     * Define threshold of rows count when sticky toolbar will enabled
     * @property {number}
     */
    stickyToolbarThreshold: 9,

    constructor: function FrontendToolbar(...args) {
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
    },

    initialize(options) {
        if (options.stickyToolbarThreshold !== void 0) {
            this.stickyToolbarThreshold = options.stickyToolbarThreshold;
        }

        FrontendToolbar.__super__.initialize.call(this, options);

        this.listenTo(this.collection, 'reset change', this.toggleStickyToolbar);
    },

    render() {
        FrontendToolbar.__super__.render.call(this);

        this.toggleStickyToolbar();
        this.noChildrenVisibility();

        return this;
    },

    toggleStickyToolbar() {
        if (this.stickyToolbarThreshold > 0 &&
            this.collection.length > this.stickyToolbarThreshold &&
            this.el.getAttribute('data-grid-toolbar')
        ) {
            this.el.classList.add('sticky', 'sticky--top');
            this.el.setAttribute('data-sticky', '');
        } else {
            this.el.classList.remove('sticky', 'sticky--top', 'in-sticky', 'scroll-up', 'scroll-down');
            this.el.removeAttribute('data-sticky');
        }
    },

    noChildrenVisibility() {
        this.$el.removeClass('no-children');
        this.$el.toggleClass('no-children', !Object.values(this.subviews)
            .some(view => document.contains(view.el) && view.$el.is(':visible')));
    }
});

export default FrontendToolbar;
