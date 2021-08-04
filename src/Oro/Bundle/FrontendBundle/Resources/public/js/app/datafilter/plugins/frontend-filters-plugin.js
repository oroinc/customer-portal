define(function(require, exports, module) {
    'use strict';

    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const ToggleFiltersAction = require('orofilter/js/actions/toggle-filters-action');
    const FullScreenFiltersAction = require('orofrontend/js/app/datafilter/actions/fullscreen-filters-action');
    const FiltersTogglePlugin = require('orofilter/js/plugins/filters-toggle-plugin');
    const viewportManager = require('oroui/js/viewport-manager');
    const config = require('module-config').default(module.id);
    const launcherOptions = _.extend({
        className: 'btn',
        icon: 'filter',
        label: __('oro.filter.datagrid-toolbar.filters'),
        ariaLabel: __('oro.filter.datagrid-toolbar.aria_label')
    }, config.launcherOptions || {});

    const FrontendFiltersTogglePlugin = FiltersTogglePlugin.extend({
        /**
         * {Object}
         */
        filtersActions: {
            tablet: FullScreenFiltersAction
        },

        /**
         * @inheritdoc
         */
        constructor: function FrontendFiltersTogglePlugin(main, options) {
            FrontendFiltersTogglePlugin.__super__.constructor.call(this, main, options);
        },

        /**
         * @returns {Function}
         * @private
         */
        _getApplicableAction: function() {
            const Action = _.find(this.filtersActions, function(action, name) {
                if (viewportManager.isApplicable({maxScreenType: name})) {
                    return action;
                }
            });

            return _.isMobile() && _.isFunction(Action) ? Action : ToggleFiltersAction;
        },

        onBeforeToolbarInit: function(toolbarOptions) {
            const Action = this._getApplicableAction();

            const options = {
                datagrid: this.main,
                launcherOptions: launcherOptions,
                order: config.order || 50
            };

            toolbarOptions.addToolbarAction(new Action(options));
        }
    });
    return FrontendFiltersTogglePlugin;
});
