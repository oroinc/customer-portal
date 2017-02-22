define(function(require) {
    'use strict';

    var FrontendGridViewsView;
    var GridViewsView = require('orodatagrid/js/datagrid/grid-views/view');

    FrontendGridViewsView = GridViewsView.extend({
        /** @property */
        template: '#template-frontend-datagrid-grid-view',

        initialize: function(options) {
            FrontendGridViewsView.__super__.initialize.call(this, options);
        }

    });

    return FrontendGridViewsView;
});
