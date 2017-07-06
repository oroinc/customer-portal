define(function(require) {
    'use strict';

    var FrontendColumnManagerView;
    var _ = require('underscore');
    var ColumnManagerView = require('orodatagrid/js/app/views/column-manager/column-manager-view');
    var FullScreenPopupView = require('orofrontend/blank/js/app/views/fullscreen-popup-view');
    var viewportManager = require('oroui/js/viewport-manager');

    FrontendColumnManagerView = ColumnManagerView.extend({
        /**
         * @property
         */
        className: 'dropdown-menu',

        /**
         * @property
         */
        viewport: {
            maxScreenType: 'mobile-landscape'
        },

        /**
         * @property
         */
        popupOptions: {
            popupBadge: true,
            popupIcon: 'fa-cog',
            popupLabel: _.__('oro_frontend.datagrid.manage_grid'),
            contentElement: null
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.viewport = _.extend(this.viewport, options.viewport || {});
            this.popupOptions.contentElement = this.$el;
            this.popupOptions = _.extend({}, this.popupOptions, options.popupOptions || {});

            FrontendColumnManagerView.__super__.initialize.call(this, options);
        },

        /**
         * @inheritDoc
         */
        updateStateView: function() {
            if (viewportManager.isApplicable(this.viewport)) {
                this.setFullScreenViewDesign(true);

                this.fullscreenView = new FullScreenPopupView(this.popupOptions);
                this.fullscreenView.on('close', function() {
                    this.setFullScreenViewDesign(false);
                    this.fullscreenView.dispose();
                    delete this.fullscreenView;
                }, this);

                this.fullscreenView.show();
            } else {
                FrontendColumnManagerView.__super__.updateStateView.apply(this, arguments);
            }
        },

        /**
         * Set design for view
         * @param {bool} apply
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

    return FrontendColumnManagerView;
});
