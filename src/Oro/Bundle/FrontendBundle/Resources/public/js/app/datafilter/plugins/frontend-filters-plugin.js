define(function(require) {
    'use strict';

    var FrontendFiltersTogglePlugin;
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var ToggleFiltersAction = require('orofilter/js/actions/toggle-filters-action');
    var FullScreenFiltersAction = require('orofrontend/js/app/datafilter/actions/fullscreen-filters-action');
    var FiltersTogglePlugin = require('orofilter/js/plugins/filters-toggle-plugin');
    var viewportManager = require('oroui/js/viewport-manager');
    var config = require('module').config();
    var launcherOptions = _.extend({
        className: 'btn',
        icon: 'filter',
        label: __('oro.filter.datagrid-toolbar.filters')
    }, config.launcherOptions || {});

    FrontendFiltersTogglePlugin = FiltersTogglePlugin.extend({
        /**
         * {Object}
         */
        filtersActions: {
            maxScreenType: 'tablet'
        },

        /**
         * @inheritDoc
         */
        constructor: function FrontendFiltersTogglePlugin() {
            FrontendFiltersTogglePlugin.__super__.constructor.apply(this, arguments);
        },

        /**
         * @returns {Function}
         * @private
         */
        _getApplicableAction: function() {
            var Action;
            if (viewportManager.isApplicable(this.filtersActions)) {
                Action = FullScreenFiltersAction;
            }

            return _.isMobile() && _.isFunction(Action) ? Action : ToggleFiltersAction;
        },

        onBeforeToolbarInit: function(toolbarOptions) {
            var Action = this._getApplicableAction();

            var options = {
                datagrid: this.main,
                launcherOptions: launcherOptions,
                order: config.order || 50
            };

            toolbarOptions.addToolbarAction(new Action(options));
        }
    });
    return FrontendFiltersTogglePlugin;
});
