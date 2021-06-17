define(function(require) {
    'use strict';

    const MapAction = require('oro/datagrid/action/map-action');
    const ViewportManager = require('oroui/js/viewport-manager');
    const Popover = require('bootstrap-popover');
    const FullscreenPopupView = require('orofrontend/blank/js/app/views/fullscreen-popup-view');

    require('jquery');

    const FrontendMapAction = MapAction.extend({
        /**
         * @property {String}
         */
        popoverTpl: '<div class="map-popover popover"><div class="arrow"></div>' +
            '<div class="map-popover__content popover-body"></div></div>',

        /**
         * @property {Object}
         */
        viewport: {
            maxScreenType: 'tablet-small'
        },

        /**
         * @inheritDoc
         */
        constructor: function FrontendMapAction(options) {
            FrontendMapAction.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
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
        * @inheritDoc
        */
        onActionClick: function(e) {
            e.preventDefault();
            if (this.mapView.map) {
                this.mapView.map.setCenter(this.mapView.location);
            }

            if (ViewportManager.isApplicable(this.viewport)) {
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
