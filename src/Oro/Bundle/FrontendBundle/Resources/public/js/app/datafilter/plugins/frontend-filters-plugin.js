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
            tablet: FullScreenFiltersAction
        },

        /**
         * @returns {Function}
         * @private
         */
        _getApplicableAction: function() {
            var Action = this.filtersActions[viewportManager.getViewport().type];

            if (_.isUndefined(Action)) {
                Action = _.find(this.filtersActions, function(action, name) {
                    if (viewportManager.isApplicable({maxScreenType: name})) {
                        return action;
                    }
                });
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
