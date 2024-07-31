import $ from 'jquery';
import ActionsPanel from 'orodatagrid/js/datagrid/actions-panel';
import template from 'tpl-loader!orofrontend/templates/datagrid/actions-panel.html';

const FrontendActionsPanel = ActionsPanel.extend({
    template,

    _attributes: {
        'data-responsive-dropdown': '',
        'data-placeholder-show-one-child-on-mobile-big': '',
        'data-input-widget-options': JSON.stringify({
            dropdownMenuClass: 'dropdown-menu--no-min-width',
            actionsContainerClass: 'dropdown-toolbar-actions',
            screenThreshold: 'mobile-big'
        })
    },

    constructor: function FrontendActionsPanel(...args) {
        FrontendActionsPanel.__super__.constructor.apply(this, args);
    },

    preinitialize: function(options) {
        this.collection = options.collection;

        if (!this.collection) {
            throw new TypeError('"collection" is required');
        }

        this.gridName = this.collection.inputName;
    },

    getTemplateData() {
        return {
            gridName: this.gridName
        };
    },

    /**
     * Renders panel
     *
     * @return {*}
     */
    render: function() {
        if (typeof this.template === 'function') {
            const isDropdown = this.$el.is('.dropdown-menu');

            this.$el.html(this.template(this.getTemplateData()));
            this.launchers.forEach(launcher => {
                launcher.setOptions({withinDropdown: isDropdown});
                this.$('[data-group="actions"]').append(launcher.render().$el);
                launcher.trigger('appended');
            });

            this.$el.trigger('content:changed');
        } else {
            FrontendActionsPanel.__super__.render.call(this);
        }

        this.launchers.forEach(launcher => {
            if (launcher.renderInExternalContainer && typeof launcher.getExternalContainer === 'function') {
                const container = launcher.getExternalContainer();
                if (!container) {
                    return;
                }
                launcher.$el.appendTo(container);
                $(container).trigger('content:changed');
                launcher.trigger('moved:externally');
            }
        });

        return this;
    }
});

export default FrontendActionsPanel;
