define(function(require) {
    'use strict';

    var FrontendMapAction;
    var _ = require('underscore');
    var MapAction = require('oro/datagrid/action/map-action');
    var ViewportManager = require('oroui/js/viewport-manager');
    var Popover = require('bootstrap-popover');
    var FullscreenPopupView = require('orofrontend/blank/js/app/views/fullscreen-popup-view');

    require('jquery');

    FrontendMapAction = MapAction.extend({
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
        constructor: function FrontendMapAction() {
            FrontendMapAction.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            FrontendMapAction.__super__.initialize.apply(this, arguments);
            this.mapView.on('mapRendered', _.bind(this.onMapRendered, this));
            this.listenTo(this.model, 'change:isDropdownActions', this.actionsDropdownListener);
        },

        onMapRendered: function() {
            var placement = this.getPopoverConfig().placement;
            var $popoverTrigger = this.subviews[0].$el;
            var popover = $popoverTrigger.data(Popover.DATA_KEY);
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
            var onClose = _.bind(function() {
                this.fullscreenView.dispose();
                delete this.fullscreenView;
            }, this);

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
                this.subviews[0].$el.on('click', _.bind(this.handleFullScreenView, this));
            }
        }
    });

    return FrontendMapAction;
});
