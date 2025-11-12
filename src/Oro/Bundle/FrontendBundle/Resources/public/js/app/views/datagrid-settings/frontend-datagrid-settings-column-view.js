import $ from 'jquery';
import _ from 'underscore';
import DatagridSettingsListView from 'orodatagrid/js/app/views/datagrid-settings-list/datagrid-settings-list-view';
import FullScreenPopupView from 'orofrontend/default/js/app/views/fullscreen-popup-view';
import viewportManager from 'oroui/js/viewport-manager';
import moduleConfig from 'module-config';

const config = {
    className: 'dropdown-menu',
    viewport: 'mobile-landscape',
    popupOptions: {},
    ...moduleConfig(module.id)
};

const FrontendDatagridSettingsColumnView = DatagridSettingsListView.extend({
    /**
     * @property
     */
    className: config.className,

    /**
     * @property
     */
    viewport: config.viewport,

    /**
     * @property
     */
    popupOptions: _.extend({}, {
        popupIcon: 'settings',
        popupLabel: _.__('oro_frontend.datagrid.manage_grid'),
        contentElement: null
    }, _.pick(config.popupOptions, 'popupIcon', 'popupLabel', 'popupCloseButton')),

    /**
     * @inheritdoc
     */
    constructor: function FrontendDatagridSettingsColumnView(options) {
        FrontendDatagridSettingsColumnView.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.viewport = _.extend(this.viewport, options.viewport || {});
        this.popupOptions.contentElement = this.$el;
        this.popupOptions = _.extend({}, this.popupOptions, options.popupOptions || {});

        FrontendDatagridSettingsColumnView.__super__.initialize.call(this, options);
    },

    /**
     * Handles bootstrap dropdown show event
     *
     * @param {jQuery.Event} showEvent
     */
    beforeOpen: function(showEvent) {
        const dropdown = $(showEvent.target).find('[data-toggle="dropdown"]').data('bs.dropdown');
        if (dropdown) {
            // prevent usage popper in dropdown, if it's fullscreen mode (_inNavbar doesn't use popper)
            dropdown._inNavbar = viewportManager.isApplicable(this.viewport) ? true : dropdown._detectNavbar();
        }
    },

    /**
     * @inheritdoc
     */
    updateStateView: function() {
        if (viewportManager.isApplicable(this.viewport)) {
            this.setFullScreenViewDesign(true);

            this.fullscreenView = new FullScreenPopupView(this.popupOptions);
            this.fullscreenView.on('close', function() {
                this.setFullScreenViewDesign(false);
                this.fullscreenView.dispose();
                delete this.fullscreenView;
                this.$el.removeClass('show');
            }, this);

            this.fullscreenView.show();
        } else {
            FrontendDatagridSettingsColumnView.__super__.updateStateView.call(this);
        }
    },

    /**
     * Set design for view
     * @param {boolean} apply
     */
    setFullScreenViewDesign: function(apply) {
        if (apply) {
            this.$el
                .removeClass(this.className)
                .addClass('fullscreen');
        } else {
            this.$el
                .removeClass('fullscreen')
                .addClass(this.className);
        }
    }
});

export default FrontendDatagridSettingsColumnView;
