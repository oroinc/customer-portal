define(function(require) {
    'use strict';

    var FrontendMapAction;
    var _ = require('underscore');
    var MapAction = require('oro/datagrid/action/map-action');
    var ViewportManager = require('oroui/js/viewport-manager');
    var FullscreenPopupView = require('orofrontend/blank/js/app/views/fullscreen-popup-view');
    require('jquery');

    FrontendMapAction = MapAction.extend({
        /**
         * @property {String}
         */
        popoverTpl: '<div class="map-popover popover"><div class="map-popover__content popover-content"></div></div>',

        /**
         * @property {Object}
         */
        viewport: {
            maxScreenType: 'tablet-small'
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            FrontendMapAction.__super__.initialize.apply(this, arguments);
            this.listenTo(this.model, 'change:isDropdownActions', this.actionsDropdownListener);
        },

        /**
        * @inheritDoc
        */
        onActionClick: function(e) {
            e.preventDefault();
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
                content: this.$mapContainerFrame
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
