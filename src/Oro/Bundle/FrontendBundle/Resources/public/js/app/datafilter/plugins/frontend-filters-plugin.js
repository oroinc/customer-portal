define(function(require) {
    'use strict';

    var FrontendFiltersTogglePlugin;
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var ToggleFiltersAction = require('orofilter/js/actions/toggle-filters-action');
    var FullScreenFiltersAction = require('orofrontend/js/app/datafilter/actions/fullscreen-filters-action');
    var FiltersTogglePlugin = require('orofilter/js/plugins/filters-toggle-plugin');
    var config = require('module').config();
    var launcherOptions = _.extend({
        className: 'btn',
        icon: 'filter',
        label: __('oro.filter.datagrid-toolbar.filters')
    }, config.launcherOptions || {});

    FrontendFiltersTogglePlugin = FiltersTogglePlugin.extend({
        useFullScreenMode: _.isMobile(),

        onBeforeToolbarInit: function(toolbarOptions) {
            var options = {
                datagrid: this.main,
                launcherOptions: launcherOptions,
                order: config.order || 50
            };


            if (this.useFullScreenMode) {
                toolbarOptions.addToolbarAction(new FullScreenFiltersAction(options));
            } else {
                toolbarOptions.addToolbarAction(new ToggleFiltersAction(options));
            }
        }
    });

    return FrontendFiltersTogglePlugin;
});
