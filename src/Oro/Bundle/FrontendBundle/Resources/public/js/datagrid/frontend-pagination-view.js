import BaseView from 'oroui/js/app/views/base/view';
import PaginationStepper from './pagination-stepper';
import PaginationInput from 'orodatagrid/js/datagrid/pagination-input';

/**
 * Datagrid pagination variant view
 *
 * @export orofrontend/js/app/datagrid/frontend-pagination-view
 * @class  orofrontend.datagrid.FrontendPaginationView
 */
const FrontendPaginationView = BaseView.extend({
    optionNames: BaseView.prototype.optionNames.concat(['pagination_threshold']),

    /**
     * @property {number}
     */
    pagination_threshold: 25,

    enabled: true,

    listen: {
        'add collection': 'render',
        'remove collection': 'render',
        'reset collection': 'render'
    },

    constructor: function FrontendPaginationView(...args) {
        FrontendPaginationView.__super__.constructor.apply(this, args);
    },

    initialize(options) {
        this.options = options;
    },

    render() {
        this.subview('paginator', this.renderPaginator({
            ...this.options,
            el: this.el
        })).render();

        if (this.hidden || this.$el.is(':empty') || this.subview('paginator').$el.is(':hidden')) {
            this.$el.addClass('hide');
        } else {
            this.$el.removeClass('hide');
        }
        return this;
    },

    /**
     * Disable pagination
     *
     * @return {*}
     */
    disable: function() {
        this.enabled = false;
        this.subview('paginator').enabled = this.enabled;
        this.render();
        return this;
    },

    /**
     * Enable pagination
     *
     * @return {*}
     */
    enable: function() {
        this.enabled = true;
        this.subview('paginator').enabled = this.enabled;
        this.render();
        return this;
    },

    /**
     * Render pagination by conditions
     * @param options
     * @returns {Constructor}
     */
    renderPaginator(options) {
        return this.isApplicable() ? new PaginationStepper(options) : new PaginationInput(options);
    },

    isApplicable() {
        const {state} = this.collection;
        return this.pagination_threshold > state.totalPages;
    }
});

export default FrontendPaginationView;
