define(function(require) {
    'use strict';

    const MapAction = require('oro/datagrid/action/map-action');
    const viewportManager = require('oroui/js/viewport-manager').default;
    const Popover = require('bootstrap-popover');
    const FullscreenPopupView = require('orofrontend/default/js/app/views/fullscreen-popup-view');
    const template = require('tpl-loader!orocustomer/templates/datagrid/action/frontend-map-action.html');

    require('jquery');

    const FrontendMapAction = MapAction.extend({
        /**
         * @property {String}
         */
        popoverTpl: template(),

        /**
         * @property {Object}
         */
        viewport: 'tablet-small',

        /**
         * @inheritdoc
         */
        constructor: function FrontendMapAction(options) {
            FrontendMapAction.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            FrontendMapAction.__super__.initialize.call(this, options);
            this.mapView.on('mapRendered', this.onMapRendered.bind(this));
            this.listenTo(this.model, 'change:isDropdownActions', this.actionsDropdownListener);
        },

        onMapRendered: function() {
            const placement = this.getPopoverConfig().placement;
            const $popoverTrigger = this.subviews[0].$el;
            const popover = $popoverTrigger.data(Popover.DATA_KEY);
            if (popover !== void 0) {
                popover.applyPlacement('', placement);
            }
        },
        /**
        * @inheritdoc
        */
        onActionClick: function(e) {
            e.preventDefault();
            if (this.mapView.map) {
                this.mapView.map.setCenter(this.mapView.location);
            }

            if (viewportManager.isApplicable(this.viewport)) {
                this.handleFullScreenView();
            } else {
                this.handlePopover(this.getPopoverConfig());
            }
        },

        handleFullScreenView: function() {
            const onClose = () => {
                this.fullscreenView.dispose();
                delete this.fullscreenView;
            };

            if (this.fullscreenView) {
                onClose();
            }

            this.fullscreenView = new FullscreenPopupView({
                contentElement: this.$mapContainerFrame,
                popupIcon: 'fa-chevron-left'
            });
            this.fullscreenView.on('close', onClose);
            this.fullscreenView.show();
            this.mapView.updateMap(this.getAddress(), this.model.get('label'));
        },

        actionsDropdownListener: function() {
            if (this.model.get('isDropdownActions')) {
                this.subviews[0].$el.on('click', this.handleFullScreenView.bind(this));
            }
        }
    });

    return FrontendMapAction;
});
