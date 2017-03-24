define(function(require) {
    'use strict';

    var FrontendMapAction;
    var _ = require('underscore');
    var mediator = require('oroui/js/mediator');
    var MapAction = require('oro/datagrid/action/map-action');
    var ViewportManager = require('oroui/js/viewport-manager');
    var FullscreenPopupView = require('orofrontend/blank/js/app/views/fullscreen-popup-view');
    require('jquery');

    FrontendMapAction = MapAction.extend({
        popoverTpl: '<div class="map-popover popover"><div class="map-popover__content popover-content"></div></div>',

        viewport: {
            maxScreenType: 'tablet-small'
        },

        initialize: function(options) {
            FrontendMapAction.__super__.initialize.apply(this, arguments);
            mediator.on('viewport:change', this.onViewportChange, this);
        },

        onViewportChange: function() {
            if (!this.subviews.length) {
                return;
            }
            mediator.trigger('datagrid:doRefresh:' + this.datagrid.name);
        },

        onGridRendered: function() {
            if (!this.subviews.length) {
                return;
            }
            if (ViewportManager.isApplicable(this.viewport)) {
                this.createFullScreenView();
            } else {
                this.createPopover();
            }
        },

        createFullScreenView: function() {
            this.subviews[0].$el.on('click', _.bind(function() {
                this.fullscreenView = new FullscreenPopupView({
                    content: this.$mapContainerFrame
                });
                this.fullscreenView.show();
                this.mapView.updateMap(this.getAddress(), this.model.get('label'));
            }, this));
        }
    });

    return FrontendMapAction;
});
