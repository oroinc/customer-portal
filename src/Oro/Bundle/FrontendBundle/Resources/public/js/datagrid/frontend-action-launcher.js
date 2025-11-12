import ActionLauncher from 'orodatagrid/js/datagrid/action-launcher';

/**
 * Frontend action launcher variant
 *
 * @export orofrontend/js/datagrid/frontend-action-launcher
 * @extends orodatagrid/js/datagrid/action-launcher
 * @class  orofrontend.datagrid.FrontendActionLauncher
 */
const FrontendActionLauncher = ActionLauncher.extend({
    hidden: false,

    renderInExternalContainer: false,

    dropdownActionWithDivider: false,

    dropdownDividerClassName: 'dropdown-item--divider',

    constructor: function FrontendActionLauncher(...args) {
        FrontendActionLauncher.__super__.constructor.apply(this, args);
    },

    setOptions(options) {
        FrontendActionLauncher.__super__.setOptions.call(this, options);

        if (options.renderInExternalContainer !== void 0) {
            this.renderInExternalContainer = options.renderInExternalContainer;
        }

        if (options.dropdownActionWithDivider !== void 0) {
            this.dropdownActionWithDivider = options.dropdownActionWithDivider;
        }

        if (options.dropdownDividerClassName !== void 0) {
            this.dropdownDividerClassName = options.dropdownDividerClassName;
        }

        return this;
    },

    render() {
        FrontendActionLauncher.__super__.render.call(this);

        this.toggleVisibility(this.hidden);

        return this;
    },

    toggleVisibility(state) {
        this.hidden = state;
        this.$el.toggleClass('hidden', state);
    },

    /**
     * @returns {Element|boolean}
     */
    getExternalContainer() {
        if (!this.renderInExternalContainer) {
            return false;
        }

        return document.querySelector(this.getExternalContainerSelector());
    },

    getExternalContainerSelector() {
        return `[data-group="external-toolbar-${this.action.datagrid.name}"]`;
    },

    isInDialogWidget() {
        return this.$el.closest('[role="dialog"]').length;
    }
});

export default FrontendActionLauncher;
