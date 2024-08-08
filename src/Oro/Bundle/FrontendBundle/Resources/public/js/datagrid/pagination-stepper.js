import PaginationInput from 'orodatagrid/js/datagrid/pagination-input';
import viewportManager from 'oroui/js/viewport-manager';
import template from 'tpl-loader!orofrontend/default/templates/datagrid/pagination-input-stepper.html';

/**
 * Datagrid pagination with steps
 *
 * @export  orofrontend/js/app/datagrid/pagination-stepper
 * @class   orofrontend.datagrid.PaginationStepper
 * @extends orodatagrid.datagrid.PaginationInput
 */
const PaginationStepper = PaginationInput.extend({
    optionNames: PaginationInput.prototype.optionNames.concat(['pagination_threshold']),

    /**
     * @property {number}
     */
    pagination_threshold: 25,

    autoFillInput: false,

    template,

    /**
     * @property {string}
     */
    gapSymbol: '...',

    link: false,

    inputEnabled: null,

    types: {
        pagesGap: 'pages-gap',
        pageTo: 'page-to',
        inputPageTo: 'input-page-to'
    },

    listen: {
        'viewport:mobile-big mediator': 'render'
    },

    events: {
        'click [data-grid-pagination-trigger-page-ellipsis]': 'onClickOnGap',
        'keyup [data-grid-pagination-trigger-page-ellipsis]': 'onKeyUp',
        'keyup [data-grid-pagination-trigger-input]': 'onKeyUpInput',
        'blur [data-grid-pagination-trigger-input]': 'onInputBlur'
    },

    constructor: function PaginationStepper(...args) {
        PaginationStepper.__super__.constructor.apply(this, args);
    },

    getTemplateData(data) {
        return {
            ...PaginationStepper.__super__.getTemplateData.call(this, data),
            gapSymbol: this.gapSymbol,
            types: this.types
        };
    },

    onKeyUpInput(event) {
        if (event.keyCode === 27) {
            event.preventDefault();
            event.stopPropagation();

            this.onInputBlur();
        }
    },

    onKeyUp(event) {
        if (event.keyCode === 32) {
            event.preventDefault();
            event.stopPropagation();

            this.showInput(event.target);
        }
    },

    onClickOnGap(event) {
        event.preventDefault();
        event.stopPropagation();

        this.showInput(event.target);
    },

    showInput(target) {
        this.$(target).tooltip('dispose');
        this.inputEnabled = this.$(event.target).data('position');
        this.render();

        this.$el.focusFirstInput();
    },

    onInputBlur() {
        if (this.inputEnabled) {
            this.inputEnabled = null;
            this.render();
        }
    },

    /**
     * @inheritDoc
     * @param event
     */
    onChangePage(event) {
        if (this.scrollToPosition) {
            this.$el.closest('html').stop().animate({scrollTop: this.scrollToPosition.top}, '500', 'swing');
        }

        if (this.$(event.target).data('grid-pagination-page-to')) {
            this.collection.getPage(this.$(event.target).data('grid-pagination-page-to'));
        }

        PaginationStepper.__super__.onChangePage.call(this, event);
    },

    /**
     * Calculate stepper pegination range
     * @param {number} firstPage
     * @param {number} currentPage
     * @param {number} lastPage
     * @param {number} totalPages
     * @param {string} gapSymbol
     * @param {number} range
     * @param {number} showAtStartEnd
     * @returns {object[]}
     */
    fullfilPagination(
        {firstPage, currentPage, lastPage, totalPages},
        {gapSymbol = this.gapSymbol, range = 1, showAtStartEnd = 5} = {}
    ) {
        const pages = Array.from({length: totalPages}, (i, value) => value + 1);

        let startThreshold = currentPage - (range + 1);
        let endThreshold = currentPage + range;

        if (startThreshold < 0) {
            startThreshold = 0;
            endThreshold += startThreshold * -1;
        }

        if (startThreshold < showAtStartEnd - range - 1) {
            endThreshold = showAtStartEnd;
        }

        if (startThreshold > totalPages - showAtStartEnd) {
            startThreshold = totalPages - showAtStartEnd;
        }

        const middle = pages.slice(startThreshold, endThreshold);
        const start = pages.slice(0, startThreshold);
        const end = pages.slice(endThreshold);

        if (start.length > 2) {
            start.splice(1, start.length - 1, gapSymbol);
        }

        if (end.length > 2) {
            end.splice(0, end.length - 1, gapSymbol);
        }

        const mapCb = (position, page) => {
            return {
                page,
                type: this.inputEnabled !== null && this.inputEnabled === position && page === gapSymbol
                    ? this.types.inputPageTo
                    : (page === gapSymbol ? this.types.pagesGap : this.types.pageTo),
                position
            };
        };

        return [
            ...start.map(mapCb.bind(this, 'start')),
            ...middle.map(mapCb.bind(this, 'middle')),
            ...end.map(mapCb.bind(this, 'end'))
        ];
    },

    /**
     * @inheritDoc
     * @param {object[]} handles
     * @returns {object[]}
     */
    makeHandles: function(handles = []) {
        const {state} = this.collection;
        const opts = {};

        if (viewportManager.isApplicable('mobile-big')) {
            opts.range = 0;
            opts.showAtStartEnd = 4;
        }

        handles.push(...this.fullfilPagination(state, opts).map(({page, position, type}) => {
            return {
                label: page,
                type,
                position,
                active: state.currentPage === page
            };
        }));

        return PaginationStepper.__super__.makeHandles.call(this, handles);
    }
});

export default PaginationStepper;
